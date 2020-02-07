<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\ProductSearch;

use Magento\CatalogStorefrontApi\Api\Data\ProductResultContainerInterface;

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
    private $errors;

    /**
     * @param array $items
     * @param array $errors
     */
    public function __construct(
        array $items,
        array $errors
    ) {
        $this->items = $items;
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
    public function getErrors(): array
    {
        return $this->errors;
    }
}
