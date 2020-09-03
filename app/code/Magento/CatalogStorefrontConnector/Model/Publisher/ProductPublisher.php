<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\Product\PublishProductsConsumer;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductRequestAttributesMapper;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ProductMapper;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;
use Magento\CatalogMessageBroker\Model\ProductDataProcessor;

/**
 * Product publisher
 *
 * Push product data for given product ids and store id to the Storefront via Import API
 * TODO: move to CatalogMessageBroker module
 */
class ProductPublisher
{
    /**
     * @var DataProviderInterface
     */
    private $productsDataProvider;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CatalogServerInterface
     */
    private $catalogServer;

    /**
     * @var ImportProductsRequestInterfaceFactory
     */
    private $importProductsRequestInterfaceFactory;

    /**
     * @var ProductDataProcessor
     */
    private $productDataProcessor;

    /**
     * @var ProductMapper
     */
    private $productMapper;

    /**
     * @var ImportProductRequestAttributesMapper
     */
    private $importProductRequestAttributesMapper;

    /**
     * @param DataProviderInterface $productsDataProvider
     * @param State $state
     * @param LoggerInterface $logger
     * @param CatalogServerInterface $catalogServer
     * @param ImportProductsRequestInterfaceFactory $importProductsRequestInterfaceFactory
     * @param ProductDataProcessor $productDataProcessor
     * @param ProductMapper $productMapper
     * @param ImportProductRequestAttributesMapper $importProductRequestAttributesMapper
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $productsDataProvider,
        State $state,
        LoggerInterface $logger,
        CatalogServerInterface $catalogServer,
        ImportProductsRequestInterfaceFactory $importProductsRequestInterfaceFactory,
        ProductDataProcessor $productDataProcessor,
        ProductMapper $productMapper,
        ImportProductRequestAttributesMapper $importProductRequestAttributesMapper,
        int $batchSize
    ) {
        $this->productsDataProvider = $productsDataProvider;
        $this->batchSize = $batchSize;
        $this->state = $state;
        $this->logger = $logger;
        $this->catalogServer = $catalogServer;
        $this->importProductsRequestInterfaceFactory = $importProductsRequestInterfaceFactory;
        $this->productDataProcessor = $productDataProcessor;
        $this->productMapper = $productMapper;
        $this->importProductRequestAttributesMapper = $importProductRequestAttributesMapper;
    }

    /**
     * Publish data to Storefront directly
     *
     * @param array $productIds
     * @param string $storeCode
     * @param string $actionType
     * @param array $overrideProducts Temporary variables to support transition period between new and old Export API
     *
     * @return void
     *
     * @throws \Exception
     * @deprecated
     */
    public function publish(
        array $productIds,
        string $storeCode,
        string $actionType,
        array $overrideProducts = []
    ): void {
        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            function () use ($productIds, $storeCode, $actionType, $overrideProducts) {
                try {
                    $this->publishEntities($productIds, $storeCode, $actionType, $overrideProducts);
                } catch (\Throwable $e) {
                    $this->logger->critical(
                        \sprintf(
                            'Error on publish product ids "%s" in store %s',
                            \implode(', ', $productIds),
                            $storeCode
                        ),
                        ['exception' => $e]
                    );
                }
            }
        );
    }

    /**
     * Publish entities to the queue
     *
     * @param array $productIds
     * @param string $storeCode
     * @param string $actionType
     * @param array $overrideProducts
     *
     * @return void
     */
    private function publishEntities(
        array $productIds,
        string $storeCode,
        string $actionType,
        array $overrideProducts = []
    ): void {
        foreach (\array_chunk($productIds, $this->batchSize) as $idsBunch) {
            // @todo eliminate calling old API when new API can provide all of the necessary data
            $productsData = $this->productsDataProvider->fetch($idsBunch, [], ['store' => $storeCode]);
            $this->logger->debug(
                \sprintf('Publish products with ids "%s" in store %s', \implode(', ', $productIds), $storeCode),
                ['verbose' => $productsData]
            );
            if (count($productsData)) {
                $this->importProducts($storeCode, array_values($productsData), $actionType, $overrideProducts);
            }
        }
    }

    /**
     * Import products into product storage.
     *
     * @param string $storeCode
     * @param array $products
     * @param string $actionType
     * @param array $overrideProducts
     *
     * @throws \Throwable
     */
    private function importProducts(
        string $storeCode,
        array $products,
        string $actionType,
        array $overrideProducts = []
    ): void {
        $newApiProducts = [];
        $attributes = [];

        foreach ($overrideProducts as $product) {
            $newApiProducts[$product['product_id']] = $product;
        }

        foreach ($products as &$product) {
            if (isset($newApiProducts[$product['entity_id']])) {
                $product = $this->productDataProcessor->merge($newApiProducts[$product['entity_id']], $product);
            }

            if ($actionType === PublishProductsConsumer::ACTION_UPDATE) {
                $attributes[] = $this->importProductRequestAttributesMapper->setData([
                    'entity_id' => $product['entity_id'],
                    'attribute_codes' => \array_keys($product),
                ])->build();
            }

            // be sure, that data passed to Import API in the expected format
            $product = $this->productMapper->setData($product)->build();
        }
        unset($product);

        /** @var ImportProductsRequestInterface $importProductRequest */
        $importProductRequest = $this->importProductsRequestInterfaceFactory->create();
        $importProductRequest->setProducts($products);
        $importProductRequest->setStore($storeCode);
        $importProductRequest->setAttributes($attributes);

        $importResult = $this->catalogServer->importProducts($importProductRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Products import is failed: "%s"', $importResult->getMessage()));
        }
    }
}
