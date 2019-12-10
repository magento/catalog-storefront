<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\ProductSearch;

use Magento\CatalogProductApi\Api\Data\ProductResultContainerInterface;

/**
 * @inheritdoc
 */
class ProductResultContainer implements ProductResultContainerInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var array
     */
    private $metaInfo;

    /**
     * @var array
     */
    private $aggregations;

    /**
     * @var array
     */
    private $errors;

    /**
     * @param array $items
     * @param array $metaInfo
     * @param array $aggregations
     * @param array $errors
     */
    public function __construct(
        array $items,
        array $metaInfo,
        array $aggregations,
        array $errors
    ) {
        $this->items = $items;
        $this->metaInfo = $metaInfo;
        $this->aggregations = $aggregations;
        $this->errors = $errors;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getAggregations(): array
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

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
