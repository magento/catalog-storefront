<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider;

/**
 * Responsible for provide attributes data for specified category id in specific scope
 * Can accept array of nested $attributes, e.g. ['name', 'is_anchor', 'product_count', ...]
 */
interface DataProviderInterface
{
    /**
     * Get $attributes data for specified $categoryId in specific $scopes
     *
     * @param int[] $categoryIds
     * @param string[] $attributes
     * @param string[] $scopes
     * @return array
     */
    public function fetch(array $categoryIds, array $attributes, array $scopes): array;
}
