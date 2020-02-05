<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProductApi\Api\Data;

/**
 * Container with response for Category Search StoreFront API request
 *
 * @see \Magento\CatalogProductApi\Api\CategorySearchInterface
 */
interface CategoryResultContainerInterface
{
    /**
     * Categories data
     *
     * @return array[]
     */
    public function getCategories(): array;

    /**
     * List of error messages in case of failure
     *
     * @return string[]
     */
    public function getErrors(): array;
}
