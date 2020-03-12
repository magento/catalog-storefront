<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\Product\Price;

use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
/**
 * Provide regular prices for simple products and final min, max prices for all products
 *
 * Return data in format:
 * [
 *   product_id => [
 *      regular_min_price => price, //actual only for simple products
 *      regular_max_price => price, //actual only for simple products
 *      final_min_price => price,
 *      final_max_price => price,
 *  ]
 * ]
 */
class SimpleProducts
{
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
     * @param ResourceConnection $resourceConnection
     * @param PriceTableResolver $priceTableResolver
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        PriceTableResolver $priceTableResolver,
        DimensionFactory $dimensionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->priceTableResolver = $priceTableResolver;
        $this->dimensionFactory = $dimensionFactory;
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

        $maxFinalPrice = $this->resourceConnection->getConnection()->getCheckSql(
            'product.type_id IN ("'. implode('","', self::SIMPLE_PRODUCT_TYPE) .'")',
            'price_index.min_price',
            'price_index.max_price'
        );

        $select = $connection->select()
            ->from(
                ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                ['entity_id']
            )->columns(
                [
                    'regular_min_price' => 'price_index.price',
                    'regular_max_price' => 'price_index.price',
                    'final_min_price' => 'price_index.min_price',
                    'final_max_price' => $maxFinalPrice
                ]
            )->joinInner(
                ['price_index' => $priceIndexTableName],
                'price_index.entity_id = product.entity_id',
                ''
            )
            ->where('product.entity_id IN (?)', $entityIds)
            ->where('price_index.customer_group_id = ?', $customerGroupId)
            ->where('price_index.website_id = ?', $websiteId);
        $statement = $this->resourceConnection->getConnection()->query($select);
        $productPrices = [];
        while ($data = $statement->fetch()) {
            $productPrices[$data['entity_id']] = $data;
        }
        return $productPrices;
    }
}