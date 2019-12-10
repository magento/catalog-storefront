<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Test\Unit\DataProvider;

use Magento\CatalogProduct\DataProvider\DataProviderInterface;

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
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $items = [];
        foreach ($productIds as $productId) {
            $items[$productId] = $attributes;
        }

        return $items;
    }
}
