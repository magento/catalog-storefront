<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

/**
 * Fetch categories data
 */
interface FetchCategoriesInterface
{
    /**
     * Fetch categories data by Ids
     *
     * @param string[] $ids
     * @return array
     */
    public function getByIds(array $ids): array;
}
