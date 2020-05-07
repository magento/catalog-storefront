<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExportApi\Api\Data\ProductInterface;

interface ProductRetrieverInterface
{
    /**
     * @param string[]
     * @return ProductInterface[]
     */
    public function retrieve(array $ids);
}
