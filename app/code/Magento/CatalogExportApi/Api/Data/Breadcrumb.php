<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Breadcrumb entity
 */
class Breadcrumb
{
    /**
     * @var int|null
     */
    private $categoryId;

    /**
     * @var string|null
     */
    private $categoryName;

    /**
     * @var int|null
     */
    private $categoryLevel;

    /**
     * @var string|null
     */
    private $categoryUrlKey;

    /**
     * @var string|null
     */
    private $categoryUrlPath;

    /**
     * Get category id
     *
     * @return int|null
     */
    public function getCategoryId() : ?int
    {
        return $this->categoryId;
    }

    /**
     * Set category id
     *
     * @param int|null $categoryId
     *
     * @return void
     */
    public function setCategoryId(?int $categoryId) : void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * Get category name
     *
     * @return string|null
     */
    public function getCategoryName() : ?string
    {
        return $this->categoryName;
    }

    /**
     * Set category name
     *
     * @param string|null $categoryName
     *
     * @return void
     */
    public function setCategoryName(?string $categoryName) : void
    {
        $this->categoryName = $categoryName;
    }

    /**
     * Get category level
     *
     * @return int|null
     */
    public function getCategoryLevel() : ?int
    {
        return $this->categoryLevel;
    }

    /**
     * Set category level
     *
     * @param int|null $categoryLevel
     *
     * @return void
     */
    public function setCategoryLevel(?int $categoryLevel) : void
    {
        $this->categoryLevel = $categoryLevel;
    }

    /**
     * Get category url key
     *
     * @return string|null
     */
    public function getCategoryUrlKey() : ?string
    {
        return $this->categoryUrlKey;
    }

    /**
     * Set category url key
     *
     * @param string|null $categoryUrlKey
     *
     * @return void
     */
    public function setCategoryUrlKey(?string $categoryUrlKey) : void
    {
        $this->categoryUrlKey = $categoryUrlKey;
    }

    /**
     * Get category url path
     *
     * @return string|null
     */
    public function getCategoryUrlPath() : ?string
    {
        return $this->categoryUrlPath;
    }

    /**
     * Set category url path
     *
     * @param string|null $categoryUrlPath
     *
     * @return void
     */
    public function setCategoryUrlPath(?string $categoryUrlPath) : void
    {
        $this->categoryUrlPath = $categoryUrlPath;
    }
}
