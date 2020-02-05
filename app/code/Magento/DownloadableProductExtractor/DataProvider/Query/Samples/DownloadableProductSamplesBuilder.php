<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableProductExtractor\DataProvider\Query\Samples;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\CatalogExtractor\DataProvider\ColumnsDataMapper;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\DownloadableProductExtractor\DataProvider\Query\DownloadableItemsBuilderInterface;

/**
 * Build Select object to fetch downloadable product samples.
 */
class DownloadableProductSamplesBuilder implements DownloadableItemsBuilderInterface
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
     * Form and return query to get samples for products.
     *
     * @param array $productIds
     * @param array $attributes
     * @param int $storeId
     * @return Select
     * @throws \Exception
     */
    public function build(array $productIds, array $attributes, int $storeId): Select
    {
        $connection = $this->resourceConnection->getConnection();

        $downloadableSampleTable = [
            'main_table' => $this->resourceConnection->getTableName('downloadable_sample')
        ];
        $catalogProductTable = [
            'product' => $this->resourceConnection->getTableName('catalog_product_entity')
        ];
        $productSampleTitlesTable = [
            'store_title' => $this->resourceConnection->getTableName('downloadable_sample_title')
        ];
        $productSampleDefaultStoreTitlesTable = [
            'default_store_title' => $this->resourceConnection->getTableName('downloadable_sample_title')
        ];

        $columns = $this->columnsDataMapper->filter($attributes, $this->getAvailableAttributes());
        $columns['sample_id'] = 'main_table.sample_id';
        $columns['entity_id'] = 'product.entity_id';

        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $samplesSelect = $connection->select()
            ->from($downloadableSampleTable, [])
            ->columns($columns)
            ->join(
                $catalogProductTable,
                \sprintf('product.%1$s = main_table.product_id', $linkField),
                []
            )
            ->joinLeft(
                $productSampleTitlesTable,
                \sprintf(
                    'store_title.sample_id = main_table.sample_id AND store_title.store_id = %1$d',
                    $storeId
                ),
                []
            )
            ->joinLeft(
                $productSampleDefaultStoreTitlesTable,
                'default_store_title.sample_id = main_table.sample_id AND (default_store_title.store_id = 0)',
                []
            )
            ->where('product.entity_id IN(?)', $productIds);

        return $samplesSelect;
    }

    /**
     * Get list of supported columns.
     *
     * @return array
     */
    private function getAvailableAttributes(): array
    {
        return [
            'sort_order' => 'main_table.sort_order',
            'title' => new \Zend_Db_Expr('IFNULL(store_title.title, default_store_title.title)'),
        ];
    }
}
