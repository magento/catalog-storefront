<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

/**
 * Fetch product data
 */
interface FetchProductsInterface
{
    /**
     * Fetch product data
     *
     * @param string[] $ids
     * @param string[] $storeViewCodes
     * @return array
     */
    public function getByIds(array $ids, array $storeViewCodes = []): array;
}
