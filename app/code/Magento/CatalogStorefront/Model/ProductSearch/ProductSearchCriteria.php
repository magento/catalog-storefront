<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\ProductSearch;

use Magento\CatalogStorefrontApi\Api\Data\ProductCriteriaInterface;

/**
 * @inheritdoc
 */
class ProductSearchCriteria implements ProductCriteriaInterface
{
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
    private $ids;

    /**
     * @param array $ids
     * @param array $scopes
     * @param array $attributes
     */
    public function __construct(
        array $ids,
        array $scopes,
        array $attributes
    ) {
        $this->ids = $ids;
        $this->scopes = $scopes;
        $this->attributes = $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getIds(): array
    {
        return $this->ids;
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
