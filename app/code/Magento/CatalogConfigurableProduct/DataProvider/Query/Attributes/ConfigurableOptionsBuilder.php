<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogConfigurableProduct\DataProvider\Query\Attributes;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\CatalogProduct\DataProvider\ColumnsDataMapper;

/**
 * Build Select object to fetch product attributes used to build configurable product
 */
class ConfigurableOptionsBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ColumnsDataMapper
     */
    private $columnsDataMapper;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param ColumnsDataMapper $columnsDataMapper
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        ColumnsDataMapper $columnsDataMapper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->columnsDataMapper = $columnsDataMapper;
    }

    /**
     * Form and return query to get $attributes for given $productIds.
     *
     * @param array $parentProductIds
     * @param array $requestedOptions
     * @param int $storeId
     * @return Select
     * @throws \Exception
     */
    public function build(array $parentProductIds, array $requestedOptions, int $storeId): Select
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resourceConnection->getConnection();

        $eavAttributeTable = $this->resourceConnection->getTableName('eav_attribute');
        $superAttributeLabelTable = $this->resourceConnection->getTableName(
            'catalog_product_super_attribute_label'
        );
        $attributeLabelTable = $this->resourceConnection->getTableName('eav_attribute_label');
        $catalogProductEntityTable = $this->resourceConnection->getTableName('catalog_product_entity');

        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $optionColumns = $this->columnsDataMapper->filter($requestedOptions, $this->getAvailableColumns());

        $configurableOptionsSelect = $connection->select()
            ->from(['main_table' =>  $this->resourceConnection->getTableName('catalog_product_super_attribute')], [])
            ->columns($optionColumns)
            ->join(
                ['attribute' => $eavAttributeTable],
                'attribute.attribute_id = main_table.attribute_id',
                []
            )
            ->join(
                ['product' => $catalogProductEntityTable],
                \sprintf('main_table.product_id = product.%1$s', $linkField),
                []
            )
            ->joinLeft(
                ['default_label' => $superAttributeLabelTable],
                'main_table.product_super_attribute_id = default_label.product_super_attribute_id ' .
                ' AND (default_label.store_id = 0)',
                []
            )
            ->joinLeft(
                ['store' => $superAttributeLabelTable],
                \sprintf(
                    'store.product_super_attribute_id = default_label.product_super_attribute_id ' .
                    'AND store.store_id = %1$d',
                    $storeId
                ),
                []
            )
            ->joinLeft(
                ['eav_attr_label' => $attributeLabelTable],
                \sprintf(
                    'eav_attr_label.attribute_id = main_table.attribute_id AND eav_attr_label.store_id = %1$d',
                    $storeId
                ),
                []
            )
            ->where('product.entity_id IN (?)', $parentProductIds);

        return $configurableOptionsSelect;
    }

    /**
     * Get list of supported columns.
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return [
            'product_id' => 'product.entity_id',
            'attribute_code' => 'attribute.attribute_code',
            'attribute_id' => 'attribute.attribute_id',
            'id' => 'main_table.product_super_attribute_id',
            'label' =>  new \Zend_Db_Expr(
                'IFNULL(eav_attr_label.value, IFNULL(store.VALUE, ' .
                'IFNULL(attribute.frontend_label, default_label.VALUE)))'
            ),
            'position' => 'main_table.position',
            'use_default' => new \Zend_Db_Expr(
                'IF(store.use_default IS NULL, default_label.use_default, store.use_default)'
            ),
        ];
    }
}
