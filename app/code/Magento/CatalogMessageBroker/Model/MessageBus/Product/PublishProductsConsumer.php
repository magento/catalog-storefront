<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

use Magento\CatalogExport\Event\Data\Entity;
use Magento\CatalogMessageBroker\Model\Converter\AttributeCodesConverter;
use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\CatalogMessageBroker\Model\Publisher\ProductPublisher;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish products into storage
 */
class PublishProductsConsumer implements ConsumerEventInterface
{
    /**
     * Action type update
     * TODO eliminate together with calling old data providers
     * @see \Magento\CatalogMessageBroker\Model\MessageBus\Category\PublishCategoriesConsumer
     */
    public const ACTION_UPDATE = 'products_update';

    /**
     * Action type import
     * TODO eliminate together with calling old data providers
     * @see \Magento\CatalogMessageBroker\Model\MessageBus\Category\PublishCategoriesConsumer
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
     * @var AttributeCodesConverter
     */
    private $attributeCodesConverter;

    /**
     * @param LoggerInterface $logger
     * @param FetchProductsInterface $fetchProducts
     * @param ProductPublisher $productPublisher
     * @param AttributeCodesConverter $attributeCodesConverter
     */
    public function __construct(
        LoggerInterface $logger,
        FetchProductsInterface $fetchProducts,
        ProductPublisher $productPublisher,
        AttributeCodesConverter $attributeCodesConverter
    ) {
        $this->logger = $logger;
        $this->fetchProducts = $fetchProducts;
        $this->productPublisher = $productPublisher;
        $this->attributeCodesConverter = $attributeCodesConverter;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities, string $scope): void
    {
        $productsData = $this->fetchProducts->execute($entities, $scope);
        $attributesArray = $this->getAttributesArray($entities);
        $importProducts = [];
        $updateProducts = [];

        foreach ($productsData as $productData) {
            $attributes = $attributesArray[$productData['product_id']];

            if (!empty($attributes)) {
                $updateProducts[$productData['product_id']] = $this->filterAttributes($productData, $attributes);
            } else {
                $importProducts[$productData['product_id']] = $productData;
            }
        }

        $this->publishProducts($importProducts, $scope, self::ACTION_IMPORT);
        $this->publishProducts($updateProducts, $scope, self::ACTION_UPDATE);
    }

    /**
     * Retrieve transformed entities attributes data (entity_id => attributes)
     *
     * @param Entity[] $entities
     *
     * @return array
     */
    private function getAttributesArray(array $entities): array
    {
        $attributesArray = [];
        foreach ($entities as $entity) {
            $attributesArray[$entity->getEntityId()] = $entity->getAttributes();
        }

        return $attributesArray;
    }

    /**
     * Filter attributes for entity update.
     *
     * @param array $productData
     * @param array $attributes
     *
     * @return array
     */
    private function filterAttributes(array $productData, array $attributes): array
    {
        return \array_filter(
            $productData,
            function ($code) use ($attributes) {
                $attributes = $this->attributeCodesConverter->convertFromCamelCaseToSnakeCase($attributes);

                return \in_array($code, $attributes) || $code === 'product_id';
            },
            ARRAY_FILTER_USE_KEY
        );
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
            $this->productPublisher->publish($products, $storeCode, $actionType);
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while publishing products: "%s"', $e));
        }
    }
}
