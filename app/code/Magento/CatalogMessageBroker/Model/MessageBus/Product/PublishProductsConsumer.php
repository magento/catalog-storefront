<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Psr\Log\LoggerInterface;

/**
 * Publish products into storage
 */
class PublishProductsConsumer implements ConsumerEventInterface
{
    /**
     * Action type update
     */
    public const ACTION_UPDATE = 'products_update';

    /**
     * Action type import
     */
    public const ACTION_IMPORT = 'products_import';

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
    public function execute(array $entities, string $scope): void
    {
        $productsData = $this->fetchProducts->execute($entities, $scope);
        $importProducts = [];
        $updateProducts = [];

        // Transform entities data into entity_id => attributes relation
        $attributesArray = [];
        foreach ($entities as $entity) {
            $attributesArray[$entity->getEntityId()] = $entity->getAttributes();
        }

        foreach ($productsData as $productData) {
            $attributes = $attributesArray[$productData['product_id']];

            if (!empty($attributes)) {
                $updateProducts[$productData['product_id']] = \array_filter(
                    $productData,
                    function ($code) use ($attributes) {
                        return \in_array($code, \array_map(function ($attributeCode) {
                            return SimpleDataObjectConverter::camelCaseToSnakeCase($attributeCode);
                        }, $attributes)) || $code === 'product_id';
                    },
                    ARRAY_FILTER_USE_KEY
                );
            } else {
                $importProducts[$productData['product_id']] = $productData;
            }
        }

        $this->publishProducts($importProducts, $scope, self::ACTION_IMPORT);
        $this->publishProducts($updateProducts, $scope, self::ACTION_UPDATE);
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
