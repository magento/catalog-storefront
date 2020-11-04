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
     * @param array $products
     * @param string $storeCode
     * @param string $actionType
     *
     * @return void
     *
     * @throws \Exception
     * @deprecated
     */
    public function publish(
        array $products,
        string $storeCode,
        string $actionType
    ): void {
        try {
            $this->publishEntities($products, $storeCode, $actionType);
        } catch (\Throwable $e) {
            $this->logger->critical(
                \sprintf(
                    'Error on publish product ids "%s" in store %s',
                    \implode(', ', array_keys($products)),
                    $storeCode
                ),
                ['exception' => $e]
            );
        }
    }

    /**
     * Publish entities to the queue
     *
     * @param array $products
     * @param string $storeCode
     * @param string $actionType
     *
     * @return void
     */
    private function publishEntities(
        array $products,
        string $storeCode,
        string $actionType
    ): void {
        foreach (\array_chunk($products, $this->batchSize) as $productsData) {
            $this->logger->debug(
                \sprintf(
                    'Publish products with ids "%s" in store %s',
                    \implode(', ', array_keys($productsData)),
                    $storeCode
                ),
                ['verbose' => $productsData]
            );
            if (count($productsData)) {
                $this->importProducts($storeCode, array_values($productsData), $actionType);
            }
        }
    }

    /**
     * Import products into product storage.
     *
     * @param string $storeCode
     * @param array $products
     * @param string $actionType
     *
     * @throws \Throwable
     */
    private function importProducts(
        string $storeCode,
        array $products,
        string $actionType
    ): void {
        $productsRequestData = [];

        foreach ($products as $product) {
            $product = array_replace_recursive(
                $product,
                $this->productDataProcessor->merge($product)
            );
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
