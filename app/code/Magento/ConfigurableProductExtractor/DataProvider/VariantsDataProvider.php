<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductExtractor\DataProvider;

use Magento\ConfigurableProductExtractor\DataProvider\Query\ProductVariantsBuilder;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Provide configurable product data. For 2 products consist of 2 attributes
 * [
 *      configurable_options: [
 *          attribute_code,
 *          label,
 *          values: [list of attribute options for specific attribute, that belongs to configurable variants],
 *     ],
 *     variants: [
 *         product: [configurable child product data],
 *         attributes: [attributes with options for specific configurable variant]
 *     ]
 */
class VariantsDataProvider implements DataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductVariantsBuilder
     */
    private $productVariantsBuilder;

    /**
     * @var ConfigurableAttributesProvider
     */
    private $configurableAttributesProvider;

    /**
     * @var AttributeOptionsProvider
     */
    private $attributeOptionsProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ProductVariantsBuilder $productVariantsQuery
     * @param ConfigurableAttributesProvider $configurableAttributesProvider
     * @param AttributeOptionsProvider $attributeOptionsProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductVariantsBuilder $productVariantsQuery,
        ConfigurableAttributesProvider $configurableAttributesProvider,
        AttributeOptionsProvider $attributeOptionsProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productVariantsBuilder = $productVariantsQuery;
        $this->configurableAttributesProvider = $configurableAttributesProvider;
        $this->attributeOptionsProvider = $attributeOptionsProvider;
    }

    /**
     * @inheritdoc
     * @throws \Zend_Db_Statement_Exception
     */
    public function fetch(array $parentProductIds, array $requestedAttributes, array $scopes): array
    {
        $select = $this->productVariantsBuilder->build($parentProductIds, $scopes);
        $products = $this->resourceConnection->getConnection()->fetchAll($select);
        if (!$products) {
            return [];
        }

        [$attributesPerProduct, $childAttributeOptions] = $this->loadAttributes(
            $parentProductIds,
            $requestedAttributes,
            $scopes,
            $products
        );

        $result = [];
        $result[] = $this->getLinkedProductIds($products);

        if (empty($requestedAttributes) || isset($requestedAttributes['variants']['attributes'])) {
            $result[] = $this->buildVariantAttributes($products, $attributesPerProduct, $childAttributeOptions);
        }
        if (empty($requestedAttributes) || isset($requestedAttributes['configurable_options'])) {
            $result[] = $this->buildConfigurableOptions($products, $attributesPerProduct, $childAttributeOptions);
        }

        return !empty($result) ? array_replace_recursive(...$result) : $result;
    }

    /**
     * Get configurable variant ids
     *
     * @param array $products
     * @return array
     */
    private function getLinkedProductIds(array $products): array
    {
        $childrenMap = [];
        foreach ($products as $child) {
            $variantId = $child['variant_id'] ?? null;
            if ($variantId) {
                $childrenMap[$child['parent_id']]['variants'][$variantId]['product'] = $variantId;
            }
        }

        return $childrenMap;
    }

    /**
     * Build configurable options
     *
     * @param array $products
     * @param array $attributesPerProduct
     * @param array $childProductAttributeOptions
     * @return array
     */
    private function buildConfigurableOptions(
        array $products,
        array $attributesPerProduct,
        array $childProductAttributeOptions
    ): array {
        $configurableOptions = [];

        $parentChildMap = [];
        foreach ($products as $product) {
            $childId = $product['variant_id'];
            $parentChildMap[$product['parent_id']][$childId] = $childId;
        }

        foreach ($attributesPerProduct as $parentId => $configurableAttributes) {
            // handle case when configurable product do not contains variations
            if (!isset($parentChildMap[$parentId])) {
                continue ;
            }
            $attributeOptionsPerAttribute = $this->convertToOptionsPerAttribute(
                $childProductAttributeOptions,
                $parentChildMap[$parentId]
            );
            foreach ($configurableAttributes as $attributeId => &$attributes) {
                $attributes['values'] = $attributeOptionsPerAttribute[$attributeId];
            }
            $configurableOptions[$parentId]['configurable_options'] = $configurableAttributes;
        }
        return $configurableOptions;
    }

    /**
     * Build attributes for given configurable variants
     *
     * @param array $products
     * @param array $attributesPerProduct
     * @param array $childProductAttributeOptions
     * @return array
     */
    private function buildVariantAttributes(
        array $products,
        array $attributesPerProduct,
        array $childProductAttributeOptions
    ): array {
        $variantAttributes = [];
        foreach ($products as $product) {
            $parentId = $product['parent_id'];
            $childId = $product['variant_id'];
            $attributeOptions = $childProductAttributeOptions[$childId] ?? [];
            $variantAttributes[$parentId]['variants'][$childId]['attributes'] = \array_map(
                function ($attribute) use ($parentId, $attributesPerProduct) {
                    $attributeId = $attribute['attribute_id'];
                    return [
                        'label' => $attribute['label'] ?? '',
                        'code' => $attributesPerProduct[$parentId][$attributeId]['attribute_code'] ?? '',
                        'value_index' => $attribute['value_index'] ?? '',
                        'attribute_id' => $attributeId
                    ];
                },
                $attributeOptions
            );
        }
        return $variantAttributes;
    }

    /**
     * Find thought child products of configurable product attribute options belonging to the same attribute
     *
     * @param array $childProductAttributeOptions
     * @param array $childIds
     * @return array
     */
    private function convertToOptionsPerAttribute(array $childProductAttributeOptions, array $childIds): array
    {
        $childrenAttributes = \array_intersect_key($childProductAttributeOptions, $childIds);
        $childrenAttributes = \array_merge(...$childrenAttributes);

        $options = [];
        foreach ($childrenAttributes as $attributeOption) {
            $options[$attributeOption['attribute_id']][$attributeOption['value_index']] = $attributeOption;
        }

        return $options;
    }

    /**
     * Load required attributes
     *
     * @param array $parentProductIds
     * @param array $requestedAttributes
     * @param array $scopes
     * @param array $products
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function loadAttributes(
        array $parentProductIds,
        array $requestedAttributes,
        array $scopes,
        array $products
    ): array {
        $attributesPerProduct = $this->configurableAttributesProvider->provide(
            $parentProductIds,
            $requestedAttributes,
            $scopes
        );
        if (!$attributesPerProduct) {
            throw new \LogicException(
                \sprintf(
                    'Can not find attributes for the following configurable products: "%s"',
                    \implode(', ', $parentProductIds)
                )
            );
        }
        $childAttributeOptions = $this->attributeOptionsProvider->provide(
            $products,
            $attributesPerProduct,
            $scopes
        );
        if (!$childAttributeOptions) {
            throw new \LogicException(
                \sprintf(
                    'Can not find attribute options for the following configurable products: "%s"',
                    \implode(', ', $parentProductIds)
                )
            );
        }
        return [$attributesPerProduct, $childAttributeOptions];
    }
}
