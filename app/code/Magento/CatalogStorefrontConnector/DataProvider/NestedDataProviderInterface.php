<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\DataProvider;

/**
 * Responsible to provide attributes data for specific scope
 */
interface NestedDataProviderInterface
{
    /**
     * Get $attributes data for specified $productIds in specific $scopes
     *
     * @param string[] $attributes
     * @param string[] $scopes
     * @param array[][] $parentData
     * @return array
     */
    public function fetch(array $attributes, array $scopes, array $parentData): array;
}
