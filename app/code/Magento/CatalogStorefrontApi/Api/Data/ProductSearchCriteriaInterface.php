<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Request criteria DTO for product search StoreFront API
 *
 * @see \Magento\CatalogStorefrontApi\Api\ProductSearchInterface
 */
interface ProductSearchCriteriaInterface
{
    /**
     * Provide search term for full text search
     *
     * @return string|null
     */
    public function getSearchTerm(): ?string;

    /**
     * Provide filtration, e.g.
     * [
     *     'price' => [
     *         'from' => 5,
     *         'to' => 20,
     *     ],
     *     'sku' => [
     *         'eq' => 'Simple'
     *     ],
     *     ...
     * ]
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Provide pagination, e.g. ['pageSize', 'currentPage']
     *
     * @return string[]
     */
    public function getPage(): array;

    /**
     * Provide sort order, e.g. ['price' => 'DESC'|'ASC', ...]
     *
     * @return array
     */
    public function getSort(): array;

    /**
     * List of scopes in format, e.g ['store' => 3]
     *
     * @return string[]
     */
    public function getScopes(): array;

    /**
     * List of requested attributes. e.g. ['id', 'sku', 'name', 'url_key']
     *
     * @return string[]
     */
    public function getAttributes(): array;

    /**
     * Requested aggregations, e.g. ['attribute_name_1', 'attribute_name_2', ...]
     * null      - retrieve all aggregations
     * []        - do not return aggregations
     * ['price'] - return only aggregation by price
     *
     * @return string[]|null
     */
    public function getAggregations(): ?array;

    /**
     * Requested meta info, e.g. ['totalCount']
     *
     * @return string[]
     */
    public function getMetaInfo(): array;
}
