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
     * @param string[] $ids
     * @return array
     */
    public function execute(array $ids);
}
