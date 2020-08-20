<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

class PublishProductsConsumer implements ConsumerEventInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FetchProductsInterface
     */
    private $fetchProducts;

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
     * @inheritdoc
     */
    public function execute(array $entityIds, string $scope): void
    {
        $productsData = $this->fetchProducts->getByIds(
            $entityIds,
            array_filter([$scope])
        );
        if (!empty($productsData)) {
            $productsPerStore = [];
            foreach ($productsData as $productData) {
                $productsPerStore[$productData['store_view_code']][$productData['product_id']] = $productData;
            }
            foreach ($productsPerStore as $storeCode => $products) {
                $this->publishProducts($products, $storeCode);
            }
        }
    }

    /**
     * Publishes products to storage
     *
     * @param array $products
     * @param string $storeCode
     * @return void
     */
    private function publishProducts(array $products, string $storeCode):void
    {
        try {
            $this->productPublisher->publish(\array_keys($products), $storeCode, $products);
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while publishing products: "%s"', $e));
        }
    }
}
