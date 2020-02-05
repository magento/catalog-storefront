<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Container with response for Product Search StoreFront API request
 *
 * @see \Magento\CatalogStorefrontApi\Api\ProductSearchInterface
 */
interface ProductResultContainerInterface
{
    /**
     * Product data
     *
     * @return string[]
     */
    public function getItems(): array;

    /**
     * Aggregations
     *
     * @return string[][]
     */
    public function getAggregations(): array;

    /**
     * Meta info, e.g. ["totalCount"]
     *
     * @return array
     */
    public function getMetaInfo(): array;

    /**
     * List of error messages in case of failure
     *
     * @return string[]
     */
    public function getErrors(): array;
}
