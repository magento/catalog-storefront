<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider;

use Magento\Framework\App\ResourceConnection;
use Magento\CatalogCategory\DataProvider\Query\ProductsCountBuilder;

/**
 * Products count data provider
 */
class ProductsCountDataProvider implements DataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductsCountBuilder
     */
    private $productsCountBuilder;

    /**
     * ProductsCountDataProvider constructor.
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
     */
    public function fetch(array $categoryIds, array $attributes, array $scopes): array
    {
        $output = [];
        $attribute = key($attributes);

        foreach ($categoryIds as $categoryId) {
            $connection = $this->resourceConnection->getConnection();
            $productCount = $connection->fetchPairs($this->productsCountBuilder->getQuery($categoryIds));

            $output[$categoryId][$attribute] = $productCount[$categoryId] ?? 0;
        }

        return $output;
    }
}
