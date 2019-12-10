<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogBundleProduct\DataProvider\Query\Items;

use Magento\CatalogProduct\DataProvider\ColumnsDataMapper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Build Select object to fetch bundle product item options.
 */
class BundleProductItemOptionsBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ColumnsDataMapper
     */
    private $columnsDataMapper;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ColumnsDataMapper $columnsDataMapper
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ColumnsDataMapper $columnsDataMapper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->columnsDataMapper = $columnsDataMapper;
    }

    /**
     * Form and return query to get bundle product item options.
     *
     * @param int[] $optionIds
     * @param int[] $parentProductIds
     * @param array $attributes
     * @return Select
     * @throws \Exception
     */
    public function build(array $optionIds, array $parentProductIds, array $attributes): Select
    {
        $connection = $this->resourceConnection->getConnection();

        $bundleItemsTable = [
            'main_table' => $this->resourceConnection->getTableName('catalog_product_bundle_selection')
        ];
        $catalogProductTable = [
            'cpe' => $this->resourceConnection->getTableName('catalog_product_entity')
        ];

        $columns = $this->columnsDataMapper->filter($attributes, $this->getAvailableAttributes());
        $columns['option_id'] = 'main_table.option_id';
        $columns['parent_id'] = 'main_table.parent_product_id';
        $columns['entity_id'] = 'cpe.entity_id';

        $itemsOptionsSelect = $connection->select()
            ->from($bundleItemsTable, [])
            ->columns($columns)
            ->join(
                $catalogProductTable,
                'main_table.product_id = cpe.entity_id',
                []
            )
            ->where('main_table.option_id IN (?)', $optionIds)
            ->where('main_table.parent_product_id IN (?)', $parentProductIds);

        return $itemsOptionsSelect;
    }

    /**
     * Get list of supported columns.
     *
     * @return array
     */
    private function getAvailableAttributes(): array
    {
        return [
            'price' => 'main_table.selection_price_value',
            'position' => 'main_table.position',
            'id' => 'main_table.selection_id',
            'quantity' => 'main_table.selection_qty',
            'qty' => 'main_table.selection_qty',
            'is_default' => 'main_table.is_default',
            'price_type' => 'main_table.selection_price_type',
            'can_change_quantity' => 'main_table.selection_can_change_qty',
        ];
    }
}
