<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

interface CategoryInterface
{
    /**
     * Get product ID
     *
     * @return ?int
     */
    public function getId(): ?int;

    /**
     * Set product ID
     *
     * @param ?int $id
     * @return void
     */
    public function setId($id);

    /**
     * Get category description
     *
     * @return string
     */
    public function getDescription() : string;

    /**
     * Set category description
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void ;

    /**
     * Get category name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set category name
     *
     * @param string $name
     * @return string
     */
    public function setName(string $name);

    /**
     * Get category path
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Set category path
     *
     * @param string $path
     * @return string
     */
    public function setPath(string $path);

    /**
     * Get category path in store
     *
     * @return string
     */
    public function getPathInStore(): string;

    /**
     * Set category path
     *
     * @param string $pathInStore
     * @return string
     */
    public function setPathInStore(string $pathInStore);

    /**
     * Get category url key
     *
     * @return string
     */
    public function getUrlKey(): string;

    /**
     * Set category path
     *
     * @param string $urlKey
     * @return string
     */
    public function setUrlKey(string $urlKey);

    /**
     * Get category url key
     *
     * @return string
     */
    public function getUrlPath(): string;

    /**
     * Set category path
     *
     * @param string $urlPath
     * @return string
     */
    public function setUrlPath(string $urlPath);

    /**
     * Get category canonical url
     *
     * @return string
     */
    public function getCanonicalUrl(): string;

    /**
     * Set category path
     *
     * @param string $canonicalUrl
     * @return void
     */
    public function setCanonicalUrl(string $canonicalUrl);

    /**
     * Get category position
     *
     * @return int
     */
    public function getPosition(): int;

    /**
     * Set category position
     *
     * @param int $position
     * @return void
     */
    public function setPosition(int $position);

    /**
     * Get category level
     *
     * @return int
     */
    public function getLevel(): int;

    /**
     * Set category level
     *
     * @param int $level
     * @return void
     */
    public function setLevel(int $level);

    /**
     * Retrieve category creation date and time.
     *
     * @return string|null
     */
    public function getCreatedAt(): string;

    /**
     * Set category creation date and time.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt);

    /**
     * Retrieve category last update date and time.
     *
     * @return string|null
     */
    public function getUpdatedAt(): string;

    /**
     * Set category last update date and time.
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt);

    /**
     * Set product count
     *
     * @param int $productCount
     * @return void
     */
    public function setProductCount(int $productCount);

    /**
     * Get product count
     *
     * @return int
     */
    public function getProductCount(): int;

    /**
     * Retrieve category default sort by
     *
     * @return string|null
     */
    public function getDefaultSortBy(): string;

    /**
     * Set category default sort by
     *
     * @param string $defaultSortBy
     * @return $this
     */
    public function setDefaultSortBy(string $defaultSortBy);

    /**
     * Retrieve category default sort by
     *
     * @return string|null
     */
    public function getBreadcrumbs(): string;

    /**
     * Set category default sort by
     *
     * @param string $breadcrumbs
     * @return $this
     */
    public function setBreadcrumbs(string $breadcrumbs);
}
