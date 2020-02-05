<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\ProductSearch;

use Magento\CatalogStorefrontApi\Api\Data\ProductSearchCriteriaInterface;

/**
 * @inheritdoc
 */
class ProductSearchCriteria implements ProductSearchCriteriaInterface
{
    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $page;

    /**
     * @var array
     */
    private $scopes;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $sort;

    /**
     * @var array|null
     */
    private $aggregations;

    /**
     * @var array
     */
    private $metaInfo;

    /**
     * @var string|null
     */
    private $searchTerm;

    /**
     * @param array $filters
     * @param array $page
     * @param array $scopes
     * @param array $attributes
     * @param array $metaInfo
     * @param array $sort
     * @param array|null $aggregations
     * @param string|null $searchTerm
     */
    public function __construct(
        array $filters,
        array $page,
        array $scopes,
        array $attributes,
        array $metaInfo,
        array $sort,
        array $aggregations = null,
        ?string $searchTerm = null
    ) {
        $this->filters = $filters;
        $this->page = $page;
        $this->scopes = $scopes;
        $this->attributes = $attributes;
        $this->metaInfo = $metaInfo;
        $this->sort = $sort;
        $this->aggregations = $aggregations;
        $this->searchTerm = $searchTerm;
    }

    /**
     * @inheritdoc
     */
    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @inheritdoc
     */
    public function getPage(): array
    {
        return $this->page;
    }

    /**
     * @inheritdoc
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @inheritdoc
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getAggregations(): ?array
    {
        return $this->aggregations;
    }

    /**
     * @inheritdoc
     */
    public function getMetaInfo(): array
    {
        return $this->metaInfo;
    }
}
