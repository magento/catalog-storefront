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
     * @deprecated this is a temporary solution that will be replaced
     * with declarative schema of mapping exported data format to storefront format
     */
    public function map(array $productData): array;
}
