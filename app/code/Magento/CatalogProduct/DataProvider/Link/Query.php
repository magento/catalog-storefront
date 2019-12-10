<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\Link;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Fetch product links.
 */
class Query
{
    /**
     * @see \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED
     */
    private const LINK_TYPE_RELATED = 1;

    /**
     * @see \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL
     */
    private const LINK_TYPE_UPSELL = 4;

    /**
     * @see \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL
     */
    private const LINK_TYPE_CROSSSELL = 5;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var array
     */
    private $linkTypes = [
        self::LINK_TYPE_RELATED,
        self::LINK_TYPE_UPSELL,
        self::LINK_TYPE_CROSSSELL,
    ];

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Return query that fetches a list of product and links for them.
     *
     * @param array $productIds
     * @return Select
     * @throws \Exception
     */
    public function getQuery(array $productIds): Select
    {
        $resourceConnection = $this->resourceConnection;
        $connection = $resourceConnection->getConnection();
        $productLinkField = $this->getLinkField('catalog_product_entity');
        $catalogProductTable = $resourceConnection->getTableName('catalog_product_entity');
        $catalogProductLinkTable = $resourceConnection->getTableName('catalog_product_link');
        $catalogProductLinkAttributeTable = $resourceConnection->getTableName('catalog_product_link_attribute');
        $catalogProductLinkAttributeIntTable = $resourceConnection->getTableName('catalog_product_link_attribute_int');
        $positionAttributeCode = 'position';

        $select = $connection->select()
            ->from(
                ['e' => $catalogProductTable],
                ['linked_product_sku' => 'e.sku']
            )
            ->join(
                ['links' => $catalogProductLinkTable],
                'links.linked_product_id = e.entity_id',
                ['links.link_type_id']
            )
            ->joinLeft(
                ['link_attribute_position_int' => $catalogProductLinkAttributeIntTable],
                'link_attribute_position_int.link_id = links.link_id',
                ['position' => 'IFNULL(link_attribute_position_int.value, 1)']
            )
            ->joinLeft(
                ['product_link_attribute' => $catalogProductLinkAttributeTable],
                'product_link_attribute.product_link_attribute_id =' .
                ' link_attribute_position_int.product_link_attribute_id' .
                ' AND product_link_attribute.product_link_attribute_code = \'' . $positionAttributeCode . '\'',
                []
            )
            ->join(
                ['product_entity_table' => $catalogProductTable],
                'links.product_id = product_entity_table.' . $productLinkField,
                [
                    'sku' => 'product_entity_table.sku',
                    'product_id' => 'product_entity_table.entity_id',
                    'linked_product_type' => 'product_entity_table.type_id',
                ]
            )
            ->where('product_entity_table.entity_id IN (?)', $productIds)
            ->where('links.link_type_id IN (?)', $this->linkTypes);

        return $select;
    }

    /**
     * Get Link field by table name.
     *
     * @param string $tableName
     * @return string
     */
    private function getLinkField(string $tableName): string
    {
        $connection = $this->resourceConnection->getConnection();
        $indexList = $connection->getIndexList($tableName);

        return $indexList[$connection->getPrimaryKeyName($tableName)]['COLUMNS_LIST'][0];
    }
}
