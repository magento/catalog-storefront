<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\Product\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Framework\EntityManager\MetadataPool;
/**
 * Provide regular prices for composite products
 * To simplify we consider by "complex" product any product, thant not "simple" or "virtual"
 *
 * Return data in format:
 * [
 *   product_id => [
 *      regular_min_price => price,
 *      regular_max_price => price,
 *  ]
 * ]
 */
class CompositeProducts
{
    /**
     * Simple product type
     */
    private const SIMPLE_PRODUCT_TYPE = ['simple', 'virtual', 'downloadable'];
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var PriceTableResolver
     */
    private $priceTableResolver;
    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;
    /**
     * @var MetadataPool
     */
    private $metadataPool;
    /**
     * @param ResourceConnection $resourceConnection
     * @param PriceTableResolver $priceTableResolver
     * @param DimensionFactory $dimensionFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        PriceTableResolver $priceTableResolver,
        DimensionFactory $dimensionFactory,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->priceTableResolver = $priceTableResolver;
        $this->dimensionFactory = $dimensionFactory;
        $this->metadataPool = $metadataPool;
    }
    /**
     * Build select object for retrieve product prices
     *
     * @param array $entityIds
     * @param int $customerGroupId
     * @param int $websiteId
     * @return array
     * @throws \Exception
     */
    public function getPrices(
        array $entityIds,
        int $customerGroupId,
        int $websiteId
    ): array {
        $connection = $this->resourceConnection->getConnection();
        $websiteDimension = $this->dimensionFactory->create(
            WebsiteDimensionProvider::DIMENSION_NAME,
            (string)$websiteId
        );
        $customerGroupDimension = $this->dimensionFactory->create(
            CustomerGroupDimensionProvider::DIMENSION_NAME,
            (string)$customerGroupId
        );
        $dimensions = [
            $websiteDimension,
            $customerGroupDimension
        ];
        $priceIndexTableName = $this->priceTableResolver->resolve('catalog_product_index_price', $dimensions);
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $select = $connection->select()
            ->from(
                ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                ['entity_id']
            )->columns(
                [
                    'regular_min_price' => 'min(price_index.price)',
                    'regular_max_price' => 'max(price_index.price)'
                ]
            )->joinInner(
                ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
                'relation.parent_id = product.' . $linkField,
                ''
            )->joinInner(
                ['price_index' => $priceIndexTableName],
                'price_index.entity_id = relation.child_id',
                ''
            )
            ->where('product.entity_id IN (?)', $entityIds)
            ->where('product.type_id NOT IN (?)', self::SIMPLE_PRODUCT_TYPE)
            ->where('price_index.customer_group_id = ?', $customerGroupId)
            ->where('price_index.website_id = ?', $websiteId)
            ->group('product.entity_id');
        $statement = $this->resourceConnection->getConnection()->query($select);
        $productPrices = [];
        while ($data = $statement->fetch()) {
            $productPrices[$data['entity_id']] = $data;
        }
        return $productPrices;
    }
}