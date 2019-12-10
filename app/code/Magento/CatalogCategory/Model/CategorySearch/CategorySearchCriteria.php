<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\Model\CategorySearch;

use Magento\CatalogCategoryApi\Api\Data\CategorySearchCriteriaInterface;

/**
 * @inheritdoc
 */
class CategorySearchCriteria implements CategorySearchCriteriaInterface
{
    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $scopes;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param array $filters
     * @param array $scopes
     * @param array $attributes
     */
    public function __construct(
        array $filters,
        array $scopes,
        array $attributes
    ) {
        $this->filters = $filters;
        $this->scopes = $scopes;
        $this->attributes = $attributes;
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
}
