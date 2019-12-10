<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategoryApi\Api\Data;

/**
 * Request criteria DTO for category search StoreFront API
 *
 * @see \Magento\CatalogCategoryApi\Api\CategorySearchInterface
 */
interface CategorySearchCriteriaInterface
{
    /**
     * Provide filtration, e.g.
     * [
     *     'id': 3
     * ]
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * List of scopes in format, e.g ['store' => 3]
     *
     * @return string[]
     */
    public function getScopes(): array;

    /**
     * List of requested attributes. e.g. ['id', 'name', 'is_anchor', 'product_count']
     *
     * @return string[]
     */
    public function getAttributes(): array;
}
