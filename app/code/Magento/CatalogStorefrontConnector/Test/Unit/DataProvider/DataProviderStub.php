<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Test\Unit\DataProvider;

use Magento\CatalogStorefrontConnector\DataProvider\DataProviderInterface;

/**
 * Stub class for test Data Provider
 */
class DataProviderStub implements DataProviderInterface
{
    /**
     * Stub method
     *
     * @param int[] $productIds
     * @param array $attributes
     * @param array $scopes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $items = [];
        $attributes['type_id'] = 'simple';
        foreach ($productIds as $productId) {
            $items[$productId] = $attributes;
        }

        return $items;
    }
}
