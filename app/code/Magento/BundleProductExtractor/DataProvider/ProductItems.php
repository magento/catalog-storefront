<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleProductExtractor\DataProvider;

use Magento\Framework\App\ResourceConnection;
use Magento\BundleProductExtractor\DataProvider\Query\Items\BundleProductItemsBuilder as QueryBuilder;

/**
 * @inheritdoc
 */
class ProductItems implements \Magento\CatalogExtractor\DataProvider\DataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var BundleProductItemOptions
     */
    private $bundleProductItemOptions;

    /**
     * @var BundleProductItemOptionProducts
     */
    private $bundleProductItemOptionProducts;

    /**
     * @var BundleProductItemOptionLabels
     */
    private $bundleProductItemOptionLabels;

    /**
     * @param ResourceConnection $resourceConnection
     * @param QueryBuilder $queryBuilder
     * @param BundleProductItemOptions $bundleProductItemOptions
     * @param BundleProductItemOptionProducts $bundleProductItemOptionProducts
     * @param BundleProductItemOptionLabels $bundleProductItemOptionLabels
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QueryBuilder $queryBuilder,
        BundleProductItemOptions $bundleProductItemOptions,
        BundleProductItemOptionProducts $bundleProductItemOptionProducts,
        BundleProductItemOptionLabels $bundleProductItemOptionLabels
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->queryBuilder = $queryBuilder;
        $this->bundleProductItemOptions = $bundleProductItemOptions;
        $this->bundleProductItemOptionProducts = $bundleProductItemOptionProducts;
        $this->bundleProductItemOptionLabels = $bundleProductItemOptionLabels;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $storeId = (int)$scopes['store'];
        $attributes = $attributes['items'] ?? [];
        $bundleItemsSelect = $this->queryBuilder->build(
            $productIds,
            $attributes,
            $storeId
        );

        $statement = $this->resourceConnection->getConnection()->query($bundleItemsSelect);
        $result = [];
        while ($row = $statement->fetch()) {
            $result[$row['entity_id']]['items'][] = $row;
        }

        $result = $this->bundleProductItemOptions->fetch($attributes, $scopes, $result);

        $productAttributes = $attributes['options']['product'] ?? [];
        $requestOptionLabel = \in_array('label', $attributes['options'] ?? [], true);
        if ($requestOptionLabel) {
            $productAttributes[] = 'name';
        }

        $result = $this->bundleProductItemOptionProducts->fetch($productAttributes, $scopes, $result);
        // TODO: handle ad-hoc solution MC-29791 - need to add product label from product (SF application)
        if ($requestOptionLabel) {
            $result = $this->bundleProductItemOptionLabels->fetch($productAttributes, $scopes, $result);
        }

        return $result;
    }
}
