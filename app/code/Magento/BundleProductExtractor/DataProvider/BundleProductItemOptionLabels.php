<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleProductExtractor\DataProvider;

use Magento\CatalogStorefrontConnector\DataProvider\NestedDataProviderInterface;

/**
 * Prepare labels for bundle item option labels.
 */
class BundleProductItemOptionLabels implements NestedDataProviderInterface
{
    /**
     * Get products for bundle item option labels.
     *
     * @param string[] $attributes
     * @param string[] $scopes
     * @param array[][] $parentData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(array $attributes, array $scopes, array $parentData): array
    {
        foreach ($parentData as $entityId => $child) {
            if (!$child['items']) {
                continue;
            }
            foreach ($child['items'] as $itemKey => $item) {
                if (!isset($item['options'])) {
                    continue;
                }
                foreach ($item['options'] as $optionKey => $option) {
                    if (isset($option['product']['name'])) {
                        $parentData[$entityId]['items'][$itemKey]['options'][$optionKey]['label'] =
                            $option['product']['name'];
                    }
                }
            }
        }

        return $parentData;
    }
}
