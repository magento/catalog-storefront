<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider;

/**
 * Responsible for provide attributes data for specified product ids in specific scope
 * Can accept array of nested $attributes, e.g. ['name', 'price' => ['min_price'], ...]
 */
interface DataProviderInterface
{
    /**
     * Get $attributes data for specified $productIds in specific $scopes
     *
     * @param int[] $productIds
     * @param string[] $attributes
     * @param string[] $scopes
     * @return array
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array;
}
