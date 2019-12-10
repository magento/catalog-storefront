<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogConfigurableProduct\DataProvider\Query\Attributes;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\CatalogProduct\DataProvider\ColumnsDataMapper;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Build Select object to fetch configurable product attribute option values
 */
class ConfigurableOptionValuesBuilder
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
     * @var MetadataPool
     */
    private $metadataPool;

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
        $this->columnsDataMapper = $columnsDataMapper;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Form and return query to get Option Values for given $attributeIds.
     *
     * @param array $requestedOptions
     * @param array $childProductIds
     * @param array $attributeIds
     * @param int $storeId
     * @return Select
     * @throws \Exception
     */
    public function build(array $requestedOptions, array $childProductIds, array $attributeIds, int $storeId): Select
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resourceConnection->getConnection();

        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $optionColumns = $this->columnsDataMapper->filter($requestedOptions, $this->getAvailableColumns());

        $optionValuesSelect = $connection->select()
            ->from(['product' => $this->resourceConnection->getTableName('catalog_product_entity')], [])
            ->columns($optionColumns)
            ->join(
                ['product_attribute' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
                'product_attribute.' . $linkField . ' = product.' . $linkField,
                []
            )->join(
                ['default_option_value' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                'default_option_value.option_id = product_attribute.value',
                []
            )->joinLeft(
                ['option_value' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                \sprintf(
                    'option_value.option_id =  product_attribute.value AND option_value.store_id = %1$d',
                    $storeId
                ),
                []
            )->where('product.entity_id IN (?)', $childProductIds)
            ->where('product_attribute.attribute_id  IN (?)', $attributeIds)
            ->where('default_option_value.store_id = 0');

        return $optionValuesSelect;
    }

    /**
     * Get list of supported columns.
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return [
            'value_index' => 'product_attribute.value',
            'label' => new \Zend_Db_Expr('IFNULL(option_value.value, default_option_value.value)'),
            'default_label' => new \Zend_Db_Expr('IFNULL(option_value.value, default_option_value.value)'),
            'store_label' =>  new \Zend_Db_Expr('IFNULL(option_value.value, default_option_value.value)'),
            'use_default_value' => new \Zend_Db_Expr('1'),
            'attribute_id' => new \Zend_Db_Expr('product_attribute.attribute_id'),
            'product_id' => new \Zend_Db_Expr('product.entity_id'),
        ];
    }
}
