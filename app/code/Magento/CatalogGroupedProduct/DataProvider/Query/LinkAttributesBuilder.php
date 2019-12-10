<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGroupedProduct\DataProvider\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Grouped product links builder class
 * Build Select to fetch 'grouped attributes' for linked products:
 *   [
 *      ['product_id', 'parent_id', 'qty', 'position'],
 *      .....
 *   ]
 */
class LinkAttributesBuilder
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
    }

    /**
     * Get select for retrieving grouped attributes for assigned product links
     *
     * @param array $groupedProducts
     * @param array $attributes
     * @param array $scopes
     * @return Select
     * @throws \Exception
     */
    public function build(array $groupedProducts, $attributes, array $scopes): Select
    {
        /** @var EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $websiteId = $this->storeManager->getStore($scopes['store'])->getWebsiteId();
        $select = $this->resourceConnection->getConnection()->select()
            ->from(
                ['product_link' => $this->resourceConnection->getTableName('catalog_product_link')],
                ['product_id' => 'linked_product_id']
            )->join(
                ['link_type' => $this->resourceConnection->getTableName('catalog_product_link_type')],
                'product_link.link_type_id = link_type.link_type_id',
                []
            )->join(
                ['catalog_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
                \sprintf('catalog_product_entity.%1$s = product_link.product_id', $linkField),
                ['parent_id' => 'entity_id']
            )->join(
                ['parent_product_website' => $this->resourceConnection->getTableName('catalog_product_website')],
                'parent_product_website.product_id = catalog_product_entity.entity_id'
                    . ' AND parent_product_website.website_id = ' . $websiteId,
                []
            )->join(
                ['link_product_website' => $this->resourceConnection->getTableName('catalog_product_website')],
                'link_product_website.product_id = product_link.linked_product_id'
                . ' AND link_product_website.website_id = ' . $websiteId,
                []
            )->where(
                'link_type.code = ?',
                'super'
            )->where('catalog_product_entity.entity_id in (?)', $groupedProducts);

        foreach ($this->getAttributesMap($attributes) as $attributeDbInfo) {
            if (!empty($attributeDbInfo)) {
                $select->joinLeft(
                    [$attributeDbInfo['table'] => $this->resourceConnection->getTableName($attributeDbInfo['table'])],
                    $attributeDbInfo['condition'],
                    $attributeDbInfo['fields']
                );
            }
        }
        return $select;
    }

    /**
     * Build linked product attributes map
     *
     * @param array $attributes
     * @return array
     */
    private function getAttributesMap(array $attributes): array
    {
        $attributesMap = [];
        foreach ($attributes as $attribute) {
            switch ($attribute) {
                case 'position':
                    $attributesMap[$attribute] = [
                        'fields' => [$attribute => 'catalog_product_link_attribute_int.value'],
                        'table' => 'catalog_product_link_attribute_int',
                        'condition' => 'catalog_product_link_attribute_int.link_id = product_link.link_id'
                    ];
                    break;
                case 'qty':
                    $attributesMap[$attribute] = [
                        'fields' => [$attribute => 'catalog_product_link_attribute_decimal.value'],
                        'table' => 'catalog_product_link_attribute_decimal',
                        'condition' => 'catalog_product_link_attribute_decimal.link_id = product_link.link_id'
                    ];
                    break;
            }
        }

        return $attributesMap;
    }
}
