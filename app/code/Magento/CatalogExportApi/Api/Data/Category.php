<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

use Magento\Framework\Model\AbstractModel;

/**
 * Product entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Category extends AbstractModel
{
    private const ID = 'id';

    private const NAME = 'name';

    private const DESCRIPTION = 'description';

    private const PATH = 'path';

    private const PATH_IN_STORE = 'path_in_store';

    private const URL_KEY = 'url_key';

    private const URL_PATH = 'url_path';

    private const CANONICAL_URL = 'canonical_url';

    private const POSITION = 'position';

    private const LEVEL = 'level';

    private const CREATED_AT = 'created_at';

    private const UPDATED_AT = 'updated_at';

    private const PRODUCT_COUNT = 'product_count';

    private const DEFAULT_SORT_BY = 'default_sort_by';

    private const BREADCRUMBS = 'breadcrumbs';

    /**
     * Get product ID
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->getData(self::ID);
    }

    /**
     * Set product ID
     *
     * @param ?int $id
     * @return void
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
    }

    /**
     * Get category description
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Set category description
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get category name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set category name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name)
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * Get category path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->getData(self::PATH);
    }

    /**
     * Set category path
     *
     * @param string $path
     * @return void
     */
    public function setPath(string $path)
    {
        $this->setData(self::PATH, $path);
    }

    /**
     * Get category path in store
     *
     * @return string
     */
    public function getPathInStore(): string
    {
        return $this->getData(self::PATH_IN_STORE);
    }

    /**
     * Set category path
     *
     * @param string $pathInStore
     * @return void
     */
    public function setPathInStore(string $pathInStore)
    {
        $this->setData(self::PATH_IN_STORE, $pathInStore);
    }

    /**
     * Get category url key
     *
     * @return string
     */
    public function getUrlKey(): string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * Set category path
     *
     * @param string $urlKey
     * @return void
     */
    public function setUrlKey(string $urlKey)
    {
        $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * Get category url key
     *
     * @return string
     */
    public function getUrlPath(): string
    {
        return $this->getData(self::URL_PATH);
    }

    /**
     * Set category path
     *
     * @param string $urlPath
     * @return void
     */
    public function setUrlPath(string $urlPath)
    {
        $this->setData(self::URL_PATH, $urlPath);
    }

    /**
     * Get category canonical url
     *
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        return $this->getData(self::CANONICAL_URL);
    }

    /**
     * Set category path
     *
     * @param string $canonicalUrl
     * @return void
     */
    public function setCanonicalUrl(string $canonicalUrl)
    {
        $this->setData(self::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * Get category position
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Set category position
     *
     * @param int $position
     * @return void
     */
    public function setPosition(int $position)
    {
        $this->setData(self::POSITION, $position);
    }

    /**
     * Get category level
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->getData(self::LEVEL);
    }

    /**
     * Set category level
     *
     * @param int $level
     * @return void
     */
    public function setLevel(int $level)
    {
        $this->setData(self::LEVEL, $level);
    }

    /**
     * Retrieve category creation date and time.
     *
     * @return string|null
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set category creation date and time.
     *
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Retrieve category last update date and time.
     *
     * @return string|null
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set category last update date and time.
     *
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt(string $updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Set product count
     *
     * @param int $productCount
     * @return void
     */
    public function setProductCount(int $productCount)
    {
        $this->setData(self::PRODUCT_COUNT, $productCount);
    }

    /**
     * Get product count
     *
     * @return int
     */
    public function getProductCount(): int
    {
        return $this->getData(self::PRODUCT_COUNT);
    }

    /**
     * Retrieve category default sort by
     *
     * @return string|null
     */
    public function getDefaultSortBy(): string
    {
        return $this->getData(self::DEFAULT_SORT_BY);
    }

    /**
     * Set category default sort by
     *
     * @param string $defaultSortBy
     * @return void
     */
    public function setDefaultSortBy(string $defaultSortBy)
    {
        $this->setData(self::DEFAULT_SORT_BY, $defaultSortBy);
    }

    /**
     * Retrieve category default sort by
     *
     * @return string|null
     */
    public function getBreadcrumbs(): string
    {
        return $this->getData(self::BREADCRUMBS);
    }

    /**
     * Set category default sort by
     *
     * @param string $breadcrumbs
     * @return void
     */
    public function setBreadcrumbs(string $breadcrumbs)
    {
        $this->setData(self::BREADCRUMBS, $breadcrumbs);
    }
}
