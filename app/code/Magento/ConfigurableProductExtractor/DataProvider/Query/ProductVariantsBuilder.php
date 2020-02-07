<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductExtractor\DataProvider\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product variants query class
 * Build Select to fetch 'configurable variation ID -> parent ID' relations:
 *   [
 *      'variant_id' => 'parent_id
 *      .....
 *   ]
 */
class ProductVariantsBuilder
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
     * Get select for retrieving child products assigned to configurable parents
     *
     * @param array $parentProducts
     * @param array $scopes
     * @return Select
     * @throws \Exception
     */
    public function build(array $parentProducts, array $scopes): Select
    {
        $websiteId = $this->storeManager->getStore($scopes['store'])->getWebsiteId();
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $select = $this->resourceConnection->getConnection()->select()
            ->from(
                ['product_link' => $this->resourceConnection->getTableName('catalog_product_super_link')],
                ['variant_id' => 'product_link.product_id']
            )->join(
                ['parent_product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'parent_product.' . $linkField . ' = product_link.parent_id',
                ['parent_id' => 'parent_product.entity_id']
            )->join(
                ['product_website' => $this->resourceConnection->getTableName('catalog_product_website')],
                'product_website.product_id = product_link.product_id'
                    . ' AND product_website.website_id = ' . $websiteId,
                []
            )->where('parent_product.entity_id in (?)', $parentProducts);

        return $select;
    }
}
