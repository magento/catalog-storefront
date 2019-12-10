<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\Query\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;
use Magento\CatalogProduct\DataProvider\Query\EavAttributeQueryBuilderFactory;

/**
 * Build SQL query for fetch product attributes
 */
class ProductAttributeQueryBuilder
{
    /**
     * List of attributes that need to be added/removed to fetch
     *
     * @var array
     */
    private static $linkedAttributeMap = [
        'small_image' => ['small_image_label', 'name'],
        'image' => ['image_label', 'name'],
        'thumbnail' => ['thumbnail_label', 'name'],
        'price' => null,
        'tier_price' => null,
    ];

    /**
     * @var array
     */
    private static $requiredProductAttributes = [
        'entity_id',
        'type_id',
        'sku'
    ];

    /**
     * @var EavAttributeQueryBuilderFactory
     */
    private $attributeQueryFactory;

    /**
     * @param EavAttributeQueryBuilderFactory $attributeQueryFactory
     * @param array $linkedAttributes
     */
    public function __construct(
        EavAttributeQueryBuilderFactory $attributeQueryFactory,
        array $linkedAttributes = []
    ) {
        $this->attributeQueryFactory = $attributeQueryFactory;
        self::$linkedAttributeMap = array_merge(self::$linkedAttributeMap, $linkedAttributes);
    }

    /**
     * Form and return query to get product eav attributes for given products
     *
     * @param int[] $productIds
     * @param array $productAttributes
     * @param int $storeId
     * @return Select
     * @throws \Exception
     */
    public function build(array $productIds, array $productAttributes, int $storeId): Select
    {
        $productAttributes = \array_merge($productAttributes, self::$requiredProductAttributes);

        $attributeQueryBuilder = $this->attributeQueryFactory->create(
            [
                'entityType' => ProductInterface::class,
                'linkedAttributes' => self::$linkedAttributeMap
            ]
        );

        return $attributeQueryBuilder->build($productIds, $productAttributes, $storeId);
    }
}
