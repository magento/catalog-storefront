<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\Publisher;

use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\Product\PublishProductsConsumer;
use Magento\CatalogMessageBroker\Model\ProductDataProcessor;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductDataRequestMapper;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterfaceFactory;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;

/**
 * Product publisher
 *
 * Push product data for given product ids and store id to the Storefront via Import API
 */
class ProductPublisher
{
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
     * @var ImportProductDataRequestMapper
     */
    private $importProductDataRequestMapper;
    /**
     * @var FetchProductsInterface
     */
    private $fetchProducts;

    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $changedEntitiesMessageBuilder;

    /**
     * @param State $state
     * @param LoggerInterface $logger
     * @param CatalogServerInterface $catalogServer
     * @param ImportProductsRequestInterfaceFactory $importProductsRequestInterfaceFactory
     * @param ProductDataProcessor $productDataProcessor
     * @param ImportProductDataRequestMapper $importProductDataRequestMapper
     * @param FetchProductsInterface $fetchProducts
     * @param ChangedEntitiesMessageBuilder $changedEntitiesMessageBuilder
     * @param int $batchSize
     */
    public function __construct(
        State $state,
        LoggerInterface $logger,
        CatalogServerInterface $catalogServer,
        ImportProductsRequestInterfaceFactory $importProductsRequestInterfaceFactory,
        ProductDataProcessor $productDataProcessor,
        ImportProductDataRequestMapper $importProductDataRequestMapper,
        FetchProductsInterface $fetchProducts,
        ChangedEntitiesMessageBuilder $changedEntitiesMessageBuilder,
        int $batchSize
    ) {
        $this->batchSize = $batchSize;
        $this->state = $state;
        $this->logger = $logger;
        $this->catalogServer = $catalogServer;
        $this->importProductsRequestInterfaceFactory = $importProductsRequestInterfaceFactory;
        $this->productDataProcessor = $productDataProcessor;
        $this->importProductDataRequestMapper = $importProductDataRequestMapper;
        $this->fetchProducts = $fetchProducts;
        $this->changedEntitiesMessageBuilder = $changedEntitiesMessageBuilder;
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
            $entitiesData = array_map(function($id) {
                return [
                    'entity_id' => $id
                ];
            }, $idsBunch);
            $message = $this->changedEntitiesMessageBuilder->build(
                $actionType,
                $entitiesData,
                $storeCode
            );
            $productsData = $this->fetchProducts->execute(
                $message->getData()->getEntities(),
                $message->getMeta()->getScope()
            );
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
        $productsRequestData = [];

        foreach ($overrideProducts as $product) {
            $newApiProducts[$product['product_id']] = $product;
        }

        foreach ($products as $product) {
            if (isset($newApiProducts[$product['product_id']])) {
                $product = $this->productDataProcessor->merge($newApiProducts[$product['product_id']], $product);
            }

            // be sure, that data passed to Import API in the expected format
            $productsRequestData[] = $this->importProductDataRequestMapper->setData(
                [
                    'product' => $product,
                    'attributes' => $actionType === PublishProductsConsumer::ACTION_UPDATE ? \array_keys($product) : [],
                ]
            )->build();
        }

        /** @var ImportProductsRequestInterface $importProductRequest */
        $importProductRequest = $this->importProductsRequestInterfaceFactory->create();
        $importProductRequest->setProducts($productsRequestData);
        $importProductRequest->setStore($storeCode);

        $importResult = $this->catalogServer->importProducts($importProductRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Products import is failed: "%s"', $importResult->getMessage()));
        }
    }
}
