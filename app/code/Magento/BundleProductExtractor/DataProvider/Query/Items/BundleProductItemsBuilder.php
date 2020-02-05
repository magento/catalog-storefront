<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleProductExtractor\DataProvider\Query\Items;

use Magento\CatalogStorefrontConnector\DataProvider\ColumnsDataMapper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Build Select object to fetch bundle product items.
 */
class BundleProductItemsBuilder
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
     * Form and return query to get bundle product items.
     *
     * @param int[] $productIds
     * @param array $attributes
     * @param int $storeId
     * @return Select
     * @throws \Exception
     */
    public function build(array $productIds, array $attributes, int $storeId): Select
    {
        $connection = $this->resourceConnection->getConnection();

        $bundleItemsTable = [
            'main_table' => $this->resourceConnection->getTableName('catalog_product_bundle_option')
        ];
        $catalogProductTable = [
            'cpe' => $this->resourceConnection->getTableName('catalog_product_entity')
        ];
        $optionValuesTable = [
            'option_value' => $this->resourceConnection->getTableName('catalog_product_bundle_option_value'),
        ];
        $optionValuesDefaultTable = [
            'option_value_default' => $this->resourceConnection->getTableName('catalog_product_bundle_option_value'),
        ];

        $columns = $this->columnsDataMapper->filter($attributes, $this->getAvailableAttributes());
        $columns['option_id'] = 'main_table.option_id';
        $columns['entity_id'] = 'cpe.entity_id';
        $columns['parent_id'] = 'main_table.parent_id';

        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $itemsSelect = $connection->select()
            ->from($bundleItemsTable, [])
            ->columns($columns)
            ->joinLeft(
                $optionValuesTable,
                \sprintf(
                    'main_table.option_id = option_value.option_id ' .
                    'AND main_table.parent_id = option_value.parent_product_id ' .
                    'AND option_value.store_id = %1$d',
                    $storeId
                ),
                []
            )
            ->joinLeft(
                $optionValuesDefaultTable,
                'main_table.option_id = option_value_default.option_id ' .
                'AND main_table.parent_id = option_value_default.parent_product_id ' .
                'AND option_value_default.store_id = 0',
                []
            )
            ->join(
                $catalogProductTable,
                \sprintf('cpe.%1$s = main_table.parent_id', $linkField),
                []
            )
            ->where('cpe.entity_id IN(?)', $productIds);

        return $itemsSelect;
    }

    /**
     * Get list of supported columns.
     *
     * @return array
     */
    private function getAvailableAttributes(): array
    {
        return [
            'option_id' => 'main_table.option_id',
            'title' => new \Zend_Db_Expr('IFNULL(option_value.title, option_value_default.title)'),
            'required' => 'main_table.required',
            'type' => 'main_table.type',
            'position' => 'main_table.position',
            'sku' => 'cpe.sku',
        ];
    }
}
