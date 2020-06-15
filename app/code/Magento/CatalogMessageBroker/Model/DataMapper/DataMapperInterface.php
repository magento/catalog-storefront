<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Data mapper for product data.
 */
interface DataMapperInterface
{
    /**
     * Map incoming data to storage format
     *
     * @param array $productData
     * @return array
     */
    public function map(array $productData): array;
}
