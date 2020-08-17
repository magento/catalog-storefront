<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
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
     * @var ProductPublisher
     */
    private $productPublisher;

    /**
     * @param LoggerInterface $logger
     * @param FetchProductsInterface $fetchProducts
     * @param ProductPublisher $productPublisher
     */
    public function __construct(
        LoggerInterface $logger,
        FetchProductsInterface $fetchProducts,
        ProductPublisher $productPublisher
    ) {
        $this->logger = $logger;
        $this->fetchProducts = $fetchProducts;
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

        if (!empty($overrides = $this->fetchProducts->getByIds($ids))) {
            $this->publishProducts($overrides);
        }

        // @todo temporary solution. Deleted products must be processed from different message in queue
        // message must be published into \Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer::process
        if (!empty($deletedProducts = $this->fetchProducts->getDeleted($ids))) {
            $this->deleteProducts($deletedProducts);
        }
    }

    /**
     * Publishes products to storage
     *
     * @param array $overrides
     */
    private function publishProducts(array $overrides) : void
    {
        $productsPerStore = [];

        foreach ($overrides as $override) {
            $productsPerStore[$override['store_view_code']][$override['product_id']] = $override;
        }

        foreach ($productsPerStore as $storeCode => $products) {
            try {
                $this->productPublisher->publish(\array_keys($products), $storeCode, $products);
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Deleted products from storage
     *
     * @param array $deletedProducts
     */
    private function deleteProducts(array $deletedProducts) : void
    {
        $productsPerStore = [];

        foreach ($deletedProducts as $product) {
            $productsPerStore[$product['store_view_code']][$product['product_id']] = $product;
        }

        foreach ($productsPerStore as $storeCode => $products) {
            try {
                $this->productPublisher->delete(\array_keys($products), $storeCode);
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }
    }
}
