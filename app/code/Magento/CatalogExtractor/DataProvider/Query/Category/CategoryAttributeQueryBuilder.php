<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\Query\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\DB\Select;
use Magento\CatalogExtractor\DataProvider\Query\EavAttributeQueryBuilderFactory;

/**
 * Build SQL query for fetch category attributes
 */
class CategoryAttributeQueryBuilder
{
    /**
     * @var EavAttributeQueryBuilderFactory
     */
    private $attributeQueryFactory;

    /**
     * @var array
     */
    private static $requiredAttributes = [
        'entity_id',
    ];

    /**
     * @param EavAttributeQueryBuilderFactory $attributeQueryFactory
     */
    public function __construct(
        EavAttributeQueryBuilderFactory $attributeQueryFactory
    ) {
        $this->attributeQueryFactory = $attributeQueryFactory;
    }

    /**
     * Form and return query to get eav attributes for given categories
     *
     * @param int[] $categoryIds
     * @param array $categoryAttributes
     * @param int $storeId
     * @return Select
     * @throws \Exception
     */
    public function build(array $categoryIds, array $categoryAttributes, int $storeId): Select
    {
        $categoryAttributes = \array_merge($categoryAttributes, self::$requiredAttributes);

        $attributeQuery = $this->attributeQueryFactory->create(
            [
                'entityType' => CategoryInterface::class
            ]
        );

        return $attributeQuery->build($categoryIds, $categoryAttributes, $storeId);
    }
}
