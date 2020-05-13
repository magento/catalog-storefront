<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

interface FetchProductsInterface
{
    /**
     * @param string[]
     * @return array
     */
    public function execute(array $ids);
}
