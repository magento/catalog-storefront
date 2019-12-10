<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDownloadableProduct\DataProvider\Query\Links;

use Magento\CatalogDownloadableProduct\DataProvider\Query\DownloadableItemsBuilderInterface;
use Magento\CatalogProduct\DataProvider\ColumnsDataMapper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Build Select object to fetch downloadable product links.
 */
class DownloadableProductLinksBuilder implements DownloadableItemsBuilderInterface
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param ColumnsDataMapper $columnsDataMapper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        ColumnsDataMapper $columnsDataMapper,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->columnsDataMapper = $columnsDataMapper;
        $this->storeManager = $storeManager;
    }

    /**
     * Form and return query to get downloadable product links.
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

        $linksTable = [
            'main_table' => $this->resourceConnection->getTableName('downloadable_link')
        ];
        $catalogProductTable = [
            'product' => $this->resourceConnection->getTableName('catalog_product_entity')
        ];
        $storeLinkTitle = [
            'store_title' => $this->resourceConnection->getTableName('downloadable_link_title'),
        ];
        $defaultStoreLinkTitle = [
            'default_store_title' => $this->resourceConnection->getTableName('downloadable_link_title'),
        ];
        $storeLinkPrice = [
            'store_price' => $this->resourceConnection->getTableName('downloadable_link_price'),
        ];
        $defaultStoreLinkPrice = [
            'default_store_price' => $this->resourceConnection->getTableName('downloadable_link_price'),
        ];

        $columns = $this->columnsDataMapper->filter($attributes, $this->getAvailableAttributes());
        $columns['link_id'] = 'main_table.link_id';
        $columns['entity_id'] = 'product.entity_id';

        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();

        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        $linksSelect = $connection->select()
            ->from($linksTable, [])
            ->columns($columns)
            ->join(
                $catalogProductTable,
                \sprintf('product.%1$s = main_table.product_id', $linkField),
                []
            )
            ->joinLeft(
                $storeLinkTitle,
                \sprintf(
                    'store_title.link_id = main_table.link_id AND store_title.store_id = %1$d',
                    $storeId
                ),
                []
            )
            ->joinLeft(
                $defaultStoreLinkTitle,
                'default_store_title.link_id = main_table.link_id AND (default_store_title.store_id = 0)',
                []
            )
            ->joinLeft(
                $storeLinkPrice,
                \sprintf(
                    'store_price.link_id = main_table.link_id AND store_price.website_id = %1$d',
                    $websiteId
                ),
                []
            )
            ->joinLeft(
                $defaultStoreLinkPrice,
                'default_store_price.link_id = main_table.link_id AND default_store_price.website_id = 0',
                []
            )
            ->where('product.entity_id IN(?)', $productIds);

        return $linksSelect;
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
            'price' => new \Zend_Db_Expr('IFNULL(store_price.price, default_store_price.price)'),
        ];
    }
}
