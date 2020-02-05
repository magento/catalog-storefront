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
     * @var BundleProductItemOptionLabels
     */
    private $bundleProductItemOptionLabels;

    /**
     * @param ResourceConnection $resourceConnection
     * @param QueryBuilder $queryBuilder
     * @param BundleProductItemOptions $bundleProductItemOptions
     * @param BundleProductItemOptionLabels $bundleProductItemOptionLabels
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QueryBuilder $queryBuilder,
        BundleProductItemOptions $bundleProductItemOptions,
        BundleProductItemOptionLabels $bundleProductItemOptionLabels
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->queryBuilder = $queryBuilder;
        $this->bundleProductItemOptions = $bundleProductItemOptions;
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

        $result = $this->getLinkedProductIds($result);
        //  TODO: MC-30893 Option label is not returned for Bundle product
        if ($requestOptionLabel) {
            $result = $this->bundleProductItemOptionLabels->fetch($productAttributes, $scopes, $result);
        }

        return $result;
    }

    /**
     * Get linked product ids
     *
     * @param array $products
     * @return array
     */
    private function getLinkedProductIds(array $products): array
    {
        foreach ($products as &$child) {
            if (!isset($child['items'])) {
                continue;
            }
            foreach ($child['items'] as &$item) {
                if (!isset($item['options'])) {
                    continue;
                }
                foreach ($item['options'] as &$option) {
                    $option['product'] = $option['entity_id'];
                }
            }
        }
        return $products;
    }
}
