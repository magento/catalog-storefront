<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogBundleProduct\DataProvider;

use Magento\CatalogProduct\DataProvider\DataProviderInterface as GeneralDataProvider;
use Magento\CatalogProduct\DataProvider\NestedDataProviderInterface;

/**
 * Prepare products for bundle item options.
 */
class BundleProductItemOptionProducts implements NestedDataProviderInterface
{
    /**
     * @var GeneralDataProvider
     */
    private $generalDataProvider;

    /**
     * @param GeneralDataProvider $generalDataProvider
     */
    public function __construct(
        GeneralDataProvider $generalDataProvider
    ) {
        $this->generalDataProvider = $generalDataProvider;
    }

    /**
     * Get products for bundle item options
     *
     * @param string[] $attributes
     * @param string[] $scopes
     * @param array[][] $parentData
     * @return array
     */
    public function fetch(array $attributes, array $scopes, array $parentData): array
    {
        $productIds = $this->getProductIds($parentData);

        $attributesData = [];
        if (!empty($attributes)) {
            $attributesData = $this->generalDataProvider->fetch($productIds, $attributes, $scopes);
        }
        foreach ($parentData as $entityId => $child) {
            foreach ($child['items'] as $itemKey => $item) {
                if (!isset($item['options'])) {
                    continue;
                }
                foreach ($item['options'] as $optionKey => $option) {
                    $optionProductId = $option['entity_id'];
                    if (isset($attributesData[$optionProductId])) {
                        $product = $attributesData[$optionProductId];
                    } else {
                        $product = $optionProductId;
                    }
                    $parentData[$entityId]['items'][$itemKey]['options'][$optionKey]['product'] = $product;
                }
            }
        }

        return $parentData;
    }

    /**
     * Find products ids in parent data.
     *
     * @param array $parentData
     * @return array
     */
    private function getProductIds(array $parentData): array
    {
        $productIds = [];

        foreach ($parentData as $child) {
            if (!isset($child['items'])) {
                continue;
            }
            foreach ($child['items'] as $item) {
                if (!isset($item['options'])) {
                    continue;
                }
                foreach ($item['options'] as $option) {
                    $productIds[] = $option['entity_id'];
                }
            }
        }

        return $productIds;
    }
}
