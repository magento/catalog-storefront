<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogConfigurableProduct\DataProvider;

use Magento\Framework\App\ResourceConnection;
use Magento\CatalogConfigurableProduct\DataProvider\Query\Attributes\ConfigurableOptionValuesBuilder;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Provide attribute options for each configurable product variant
 * For given pair of parent-child products [[parent_id, variant_id], ...]
 * and attributes per product [parent_id => [attribute], ...] return attribute options assigned to products in format
 * [
 *  child_id => [
 *      attribute_id => [
 *          product_id
 *          attribute_id
 *          attribute_id
 *          value_index
 *          ...
 *      ]
 * ]
 *
 * ]
 */
class AttributeOptionsProvider
{
    private const ATTRIBUTES = [
        // "system" attributes
        'attribute_id',
        'product_id',

        // entity attributes
        'value_index',
        'default_label',
        'label',
        'store_label',
        'use_default_value',
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ConfigurableOptionValuesBuilder
     */
    private $configurableOptionValuesBuilder;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ConfigurableOptionValuesBuilder $configurableOptionValuesBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ConfigurableOptionValuesBuilder $configurableOptionValuesBuilder
    ) {
        $this->configurableOptionValuesBuilder = $configurableOptionValuesBuilder;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get configurable attribute options
     *
     * @param array $products
     * @param array $attributesPerProduct
     * @param array $scopes
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function provide(
        array $products,
        array $attributesPerProduct,
        array $scopes
    ): array {
        $storeId = (int)$scopes['store'];
        $childProductIds = [];
        $attributeIds = [];
        $childProductAttributes = [];
        foreach ($products as $product) {
            $childId = $product['variant_id'];
            $parentId = $product['parent_id'];
            if (isset($attributesPerProduct[$parentId]) && !isset($childProductAttributes[$childId])) {
                $attributes = \array_column($attributesPerProduct[$parentId], 'attribute_id');
                $childProductAttributes[$childId] = $attributes;
                $childProductIds[] = $childId;
                $attributeIds[] = $attributes;
            }
        }
        $attributeIds = \array_unique(\array_merge(...$attributeIds));

        $optionValuesSelect = $this->configurableOptionValuesBuilder->build(
            self::ATTRIBUTES,
            $childProductIds,
            $attributeIds,
            $storeId
        );
        /** @var AdapterInterface $connection */
        $statement = $this->resourceConnection->getConnection()->query($optionValuesSelect);
        $attributeOptionsValues = [];
        while ($row = $statement->fetch()) {
            $childId = $row['product_id'];
            if (\in_array($row['attribute_id'], $childProductAttributes[$childId], true)) {
                $attributeOptionsValues[$childId][$row['attribute_id']] = $row;
            }
        }

        return $attributeOptionsValues;
    }
}
