<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider;

use Magento\CatalogCategory\DataProvider\Query\CategoriesBuilder;
use Magento\CatalogCategory\DataProvider\Attributes\CategoryAttributes;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * @inheritdoc
 */
class CategoryDataProvider implements DataProviderInterface
{
    /**
     * Required attributes for category entity
     */
    private const REQUIRED_ATTRIBUTES = [
        'entity_id'
    ];

    /**
     * @var CategoryAttributes
     */
    private $categoryAttributes;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategoriesBuilder
     */
    private $categoriesBuilder;

    /**
     * @var array
     */
    private $allAttributes;

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @param CategoryAttributes $categoryAttributes
     * @param ResourceConnection $resourceConnection
     * @param CategoriesBuilder $categoriesBuilder
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        CategoryAttributes $categoryAttributes,
        ResourceConnection $resourceConnection,
        CategoriesBuilder $categoriesBuilder,
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->categoryAttributes = $categoryAttributes;
        $this->resourceConnection = $resourceConnection;
        $this->categoriesBuilder = $categoriesBuilder;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Retrieve category and category child items data
     *
     * Will build category tree based on requested attributes for provided category and it's child items
     *
     * @inheritdoc
     * @throws \Exception
     */
    public function fetch(array $categoryIds, array $attributes, array $scope): array
    {
        $output = $this->getCategories(
            $categoryIds,
            $attributes,
            $scope
        );
        return $output;
    }

    /**
     * Prepare categories data array by passed category ID's, requested attributes list and scopes
     *
     * @param array $categoryIds
     * @param array $attributes
     * @param array $scope
     * @return array
     * @throws \Exception
     */
    private function getCategories(array $categoryIds, array $attributes, array $scope): array
    {
        $storeId = (int)$scope['store'];
        $categories = $this->categoryAttributes->getAttributesData(
            $categoryIds,
            $this->processAttributes($attributes),
            $storeId
        );

        return $categories;
    }

    /**
     * Process attributes.
     *
     * @param array $attributes
     * @return array
     */
    private function processAttributes(array $attributes)
    {
        $attributeCodes = empty($attributes)
            ? $this->getAttributes()
            : array_keys($attributes);

        return array_unique($attributeCodes, SORT_REGULAR);
    }

    /**
     * Get category data for specified category id.
     *
     * @param int $categoryId
     * @param array $scope
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryData(
        int $categoryId,
        array $scope
    ): array {
        $connection = $this->resourceConnection->getConnection();
        $select = $this->categoriesBuilder->getCategoriesQuery($categoryId, $scope);
        $categories = $connection->fetchAll($select);

        return $categories;
    }

    /**
     * Get all category attributes that have to be indexed
     *
     * @return string[]
     */
    private function getAttributes(): array
    {
        if ($this->allAttributes === null) {
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection $categoryAttributes */
            $categoryAttributes = $this->attributeCollectionFactory->create();

            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            foreach ($categoryAttributes->getItems() as $attribute) {
                $this->allAttributes[] = $attribute->getAttributeCode();
            }
            $this->allAttributes = array_merge($this->allAttributes, self::REQUIRED_ATTRIBUTES);
        }

        return $this->allAttributes;
    }
}
