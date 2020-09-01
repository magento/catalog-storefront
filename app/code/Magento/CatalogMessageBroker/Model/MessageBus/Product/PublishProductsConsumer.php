<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventData;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Psr\Log\LoggerInterface;

/**
 * Publish products into storage
 */
class PublishProductsConsumer implements ConsumerEventInterface
{
    public const ACTION_UPDATE = 'update';
    public const ACTION_IMPORT = 'import';

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
    public function execute(EventData $eventData): void
    {
        $productsData = $this->fetchProducts->execute($eventData);
        $eventEntities = $eventData->getEntities();
        $importProducts = [];
        $updateProducts = [];

        foreach ($productsData as $productData) {
            $eventProduct = $eventEntities[$productData['product_id']];

            if (!empty($eventProduct->getAttributes())) {
                $updateProducts[$productData['product_id']] = \array_filter(
                    $productData,
                    function ($code) use ($eventProduct) {
                        return \in_array($code, \array_map(function ($attributeCode) {
                            return SimpleDataObjectConverter::camelCaseToSnakeCase($attributeCode);
                        }, $eventProduct->getAttributes()));
                    },
                    ARRAY_FILTER_USE_KEY
                );
            } else {
                $importProducts[$productData['product_id']] = $productData;
            }
        }

        $this->publishProducts($importProducts, $eventData->getScope(), self::ACTION_IMPORT);
        $this->publishProducts($updateProducts, $eventData->getScope(), self::ACTION_UPDATE);
    }

    /**
     * Publishes products to storage
     *
     * @param array $products
     * @param string $storeCode
     * @param string $actionType
     *
     * @return void
     */
    private function publishProducts(array $products, string $storeCode, string $actionType): void
    {
        try {
            $this->productPublisher->publish(\array_keys($products), $storeCode, $actionType, $products);
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while publishing products: "%s"', $e));
        }
    }
}
