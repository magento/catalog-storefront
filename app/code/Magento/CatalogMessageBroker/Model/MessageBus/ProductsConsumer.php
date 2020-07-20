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
        $dataPerType = [];
        $overrides = $this->fetchProducts->execute($ids);

        // @todo eliminate store manager
        $stores = $this->storeManager->getStores(true);
        $storesToIds = [];
        foreach ($stores as $store) {
            $storesToIds[$store->getCode()] = $store->getId();
        }

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
}
