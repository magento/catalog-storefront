<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\CategorySearch;

use Magento\CatalogStorefrontApi\Api\Data\CategoryResultContainerInterface;

/**
 * @inheritdoc
 */
class CategoryResultContainer implements CategoryResultContainerInterface
{
    /**
     * @var array
     */
    private $categories;

    /**
     * @var array
     */
    private $errors;

    /**
     * @param array $errors
     * @param array $categories
     */
    public function __construct(
        array $errors,
        array $categories
    ) {
        $this->errors = $errors;
        $this->categories = $categories;
    }

    /**
     * @inheritdoc
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
