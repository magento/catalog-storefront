<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogStorefront\Model;

use Magento\CatalogExport\Api\Data\ProductInterface;

interface ProductRetrieverInterface
{
    /**
     * @param string[]
     * @return ProductInterface[]
     */
    public function retrieve(array $ids);
}
