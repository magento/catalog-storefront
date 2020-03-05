<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider;

use Magento\Framework\App\ResourceConnection;
use Magento\CategoryExtractor\DataProvider\Query\ProductsCountBuilder;

/**
 * Products count data provider
 */
class ProductsCountDataProvider implements DataProviderInterface
{
    /**
     * Product count attribute code
     */
    private const ATTRIBUTE = 'product_count';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductsCountBuilder
     */
    private $productsCountBuilder;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ProductsCountBuilder $productsCountBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductsCountBuilder $productsCountBuilder
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productsCountBuilder = $productsCountBuilder;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function fetch(array $categoryIds, array $attributes, array $scopes): array
    {
        $output = [];
        $attribute = !empty($attributes) ? key($attributes) : self::ATTRIBUTE;
        $connection = $this->resourceConnection->getConnection();
        $productsCount = $connection->fetchPairs(
            $this->productsCountBuilder->getQuery($categoryIds, (int)$scopes['store'])
        );
        foreach ($productsCount as $categoryId => $productCount) {
            $output[$categoryId][$attribute] = (string)$productCount;
        }

        return $output;
    }
}
