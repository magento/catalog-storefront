<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;

/**
 * Process product update messages and update storefront app
 */
class ProductsConsumer
{
    /**
     * @var FetchProductsInterface
     */
    private $fetchProducts;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductPublisher
     */
    private $productPublisher;

    /**
     * @param LoggerInterface $logger
     * @param FetchProductsInterface $fetchProducts
     * @param StoreManagerInterface $storeManager
     * @param ProductPublisher $productPublisher
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LoggerInterface $logger,
        FetchProductsInterface $fetchProducts,
        StoreManagerInterface $storeManager,
        ProductPublisher $productPublisher
    ) {
        $this->logger = $logger;
        $this->fetchProducts = $fetchProducts;
        $this->storeManager = $storeManager;
        $this->productPublisher = $productPublisher;
    }

    /**
     * Process message
     *
     * @param string $ids
     */
    public function processMessage(string $ids)
    {
        $ids = json_decode($ids, true);

        // @todo eliminate store manager
        $stores = $this->storeManager->getStores(true);
        $storesToIds = [];
        foreach ($stores as $store) {
            $storesToIds[$store->getCode()] = $store->getId();
        }
        $overrides = $this->fetchProducts->execute($ids);
        if (!empty($overrides)) {
            $this->publishProducts($overrides, $storesToIds);
        }
        // @todo temporary solution. Deleted products must be processed from different message in queue
        // message must be published into \Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer::process
        $deletedProducts = $this->fetchProducts->getDeleted($ids);
        if (!empty($deletedProducts)) {
            $this->deleteProducts($deletedProducts, $storesToIds);
        }
    }

    /**
     * Publishes products to storage
     *
     * @param array $overrides
     * @param array $storesToIds
     */
    private function publishProducts(array $overrides, array $storesToIds)
    {
        $productsPerStore = [];
        foreach ($overrides as $override) {
            $storeId = $storesToIds[$override['store_view_code']];
            $productsPerStore[$storeId][$override['product_id']] = $override;
        }
        foreach ($productsPerStore as $storeId => $products) {
            try {
                $this->productPublisher->publish(\array_keys($products), $storeId, $products);
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Deleted products from storage
     *
     * @param array $deletedProducts
     * @param array $storesToIds
     */
    private function deleteProducts(array $deletedProducts, array $storesToIds)
    {
        $productsPerStore = [];
        foreach ($deletedProducts as $product) {
            $storeId = $storesToIds[$product['store_view_code']];
            $productsPerStore[$storeId][$product['product_id']] = $product;
        }
        foreach ($productsPerStore as $storeId => $products) {
            try {
                $this->productPublisher->delete(\array_keys($products), $storeId);
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }
    }
}
