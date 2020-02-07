<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider\Query;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Build and return query to get category data
 */
class CategoriesBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Config $eavConfig
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Config $eavConfig,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Returns query to get requested category by id
     *
     * @param int $categoryId
     * @param array $scope
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCategoriesQuery(
        int $categoryId,
        array $scope
    ): Select {
        $connection = $this->resourceConnection->getConnection();
        $categoryTable = $this->resourceConnection->getTableName('catalog_category_entity');

        $select = $connection->select()
            ->from(['c' => $categoryTable])
            ->where('c.entity_id = ?', $categoryId)
            ->columns(
                [
                    'relevant_path' => new Expression('CAST(c.entity_id as CHAR)')
                ]
            );
        $storeId = (int)$scope['store'];
        $this->joinIsActiveAttribute($select, $storeId, 1);

        return $select;
    }

    /**
     * Returns query to get children categories by parent id
     *
     * @param int $categoryId
     * @param array $scope
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getChildrenQuery(
        int $categoryId,
        array $scope
    ): Select {
        $connection = $this->resourceConnection->getConnection();
        $categoryTable = $this->resourceConnection->getTableName('catalog_category_entity');

        $subQuery = $connection->select()
            ->from(['c' => $categoryTable], [])
            ->join(
                ['p' => $categoryTable],
                "c.path LIKE CONCAT(p.path, '/%')",
                []
            )
            ->where('p.entity_id = ?', $categoryId)
            ->columns(
                [
                    'paths' => new Expression(
                        "CONCAT(GROUP_CONCAT(c.path  SEPARATOR '$|'), '$|', GROUP_CONCAT(c.path  SEPARATOR '/|'))"
                    )
                ]
            );
        $storeId = (int)$scope['store'];
        $this->joinIsActiveAttribute($subQuery, $storeId, 0);

        $children = $connection->select()
            ->from(['c' => $categoryTable])
            ->join(
                ['p' => $categoryTable],
                "c.path LIKE CONCAT(p.path, '/%')",
                []
            )
            ->where('p.entity_id = ?', $categoryId)
            ->where('c.path NOT REGEXP (?)', $connection->getIfNullSql($subQuery, "'no-path'"))
            ->columns(
                [
                    'relevant_path' => new Expression(
                        "SUBSTR(c.path, LENGTH(p.path) - LENGTH(CAST(p.entity_id as CHAR)) + 1)"
                    )
                ]
            );
        return $children;
    }

    /**
     * Join is_active attribute to select
     *
     * @param Select $select
     * @param int $storeId
     * @param int $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function joinIsActiveAttribute(Select $select, int $storeId, int $value): void
    {
        $attribute = $this->eavConfig->getAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            CategoryInterface::KEY_IS_ACTIVE
        );
        $attributeId = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $connection = $this->resourceConnection->getConnection();
        $linkFieldId = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();

        $defaultAlias = 'default_is_active';
        $storeAlias = 'store_is_active';

        $select->join(
            [$defaultAlias => $attributeTable],
            "{$defaultAlias}.{$linkFieldId} = c.{$linkFieldId} AND {$defaultAlias}.attribute_id = {$attributeId}" .
            " AND {$defaultAlias}.store_id = 0",
            []
        );
        $select->joinLeft(
            [$storeAlias => $attributeTable],
            "{$storeAlias}.{$linkFieldId} = c.{$linkFieldId} AND {$storeAlias}.attribute_id = {$attributeId}" .
            " AND {$storeAlias}.store_id = {$storeId}",
            []
        );
        $whereExpression = $connection->getCheckSql(
            $connection->getIfNullSql("{$storeAlias}.value_id", -1) . ' > 0',
            "{$storeAlias}.value",
            "{$defaultAlias}.value"
        );

        $select->where("{$whereExpression} = ?", $value);
    }
}
