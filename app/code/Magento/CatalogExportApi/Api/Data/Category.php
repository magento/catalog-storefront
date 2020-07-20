<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Category entity
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Category
{
    /**
     * @var ?int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var ?string
     */
    private $description;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $pathInStore;

    /**
     * @var string
     */
    private $urlKey;

    /**
     * @var string
     */
    private $urlPath;

    /**
     * @var string
     */
    private $canonicalUrl;

    /**
     * @var int
     */
    private $position;

    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $createdAt;

    /**
     * @var string
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $storeViewCode;

    /**
     * @var ?int
     */
    private $productCount;

    /**
     * @var ?int
     */
    private $defaultSortBy;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\Breadcrumb[]|null
     */
    private $breadcrumbs;

    /**
     * @var string[]|null
     * TODO: ad-hoc solution
     */
    private $children;

    /**
     * @var string|null
     */
    private $image;

    /**
     * @var int
     */
    private $isActive;

    /**
     * @var int|null
     */
    private $isAnchor;

    /**
     * @var string|null
     */
    private $displayMode;

    /**
     * @var string[]|null
     */
    private $availableSortBy;

    /**
     * @var string|null
     */
    private $metaTitle;

    /**
     * @var string|null
     */
    private $metaKeywords;

    /**
     * @var string|null
     */
    private $metaDescription;

    /**
     * @var int|null
     */
    private $includeInMenu;

    /**
     * Get product ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set product ID
     *
     * @param string $id
     * @return void
     */
    public function setId(?string $id)
    {
        $this->id = $id;
    }

    /**
     * Get category description
     *
     * @return string
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }

    /**
     * Set category description
     *
     * @param string $description
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Get category name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set category name
     *
     * @param string|null $name
     * @return void
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get category path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set category path
     *
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Get category path in store
     *
     * @return string
     */
    public function getPathInStore(): string
    {
        return $this->pathInStore;
    }

    /**
     * Set category path in store
     *
     * @param string $pathInStore
     * @return void
     */
    public function setPathInStore(string $pathInStore)
    {
        $this->pathInStore = $pathInStore;
    }

    /**
     * Get category url key
     *
     * @return string
     */
    public function getUrlKey(): ?string
    {
        return $this->urlKey;
    }

    /**
     * Set category path
     *
     * @param string $urlKey
     * @return void
     */
    public function setUrlKey(?string $urlKey): void
    {
        $this->urlKey = $urlKey;
    }

    /**
     * Get category url key
     *
     * @return string|null
     */
    public function getUrlPath(): ?string
    {
        return $this->urlPath;
    }

    /**
     * Set category path
     *
     * @param string $urlPath
     * @return void
     */
    public function setUrlPath(?string $urlPath): void
    {
        $this->urlPath = $urlPath;
    }

    /**
     * Get category canonical url
     *
     * @return string
     */
    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    /**
     * Set category path
     *
     * @param string $canonicalUrl
     * @return void
     */
    public function setCanonicalUrl(?string $canonicalUrl): void
    {
        $this->canonicalUrl = $canonicalUrl;
    }

    /**
     * Get category position
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Set category position
     *
     * @param int $position
     * @return void
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * Get category level
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Set category level
     *
     * @param int $level
     * @return void
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * Retrieve category creation date and time.
     *
     * @return string|null
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * Set category creation date and time.
     *
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Retrieve category last update date and time.
     *
     * @return string|null
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * Set category last update date and time.
     *
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt(string $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Set product count
     *
     * @param int $productCount
     * @return void
     */
    public function setProductCount(?int $productCount)
    {
        $this->productCount = $productCount;
    }

    /**
     * Get product count
     *
     * @return int
     */
    public function getProductCount(): ?int
    {
        return $this->productCount;
    }

    /**
     * Retrieve category default sort by
     *
     * @return string|null
     */
    public function getDefaultSortBy(): ?string
    {
        return $this->defaultSortBy;
    }

    /**
     * Set category default sort by
     *
     * @param string $defaultSortBy
     * @return void
     */
    public function setDefaultSortBy(string $defaultSortBy)
    {
        $this->defaultSortBy = $defaultSortBy;
    }

    /**
     * Retrieve category breadcrumbs
     *
     * @return \Magento\CatalogExportApi\Api\Data\Breadcrumb[]|null
     */
    public function getBreadcrumbs(): ?array
    {
        return $this->breadcrumbs;
    }

    /**
     * Set category breadcrumbs
     *
     * @param \Magento\CatalogExportApi\Api\Data\Breadcrumb[]|null $breadcrumbs
     *
     * @return void
     */
    public function setBreadcrumbs(?array $breadcrumbs): void
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * Get store view code
     *
     * @return string
     */
    public function getStoreViewCode(): string
    {
        return $this->storeViewCode;
    }

    /**
     * Set store view code
     *
     * @param string $storeViewCode
     * @return void
     */
    public function setStoreViewCode(string $storeViewCode): void
    {
        $this->storeViewCode = $storeViewCode;
    }

    /**
     * Set categories
     *
     * @param string[]|null $children
     * @return void
     */
    public function setChildren(?array $children): void
    {
        $this->children = $children;
    }

    /**
     * Get categories
     *
     * @return string[]|null
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    /**
     * @param string|null $image
     * @return void
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Get category is anchor
     *
     * @return int|null
     */
    public function getIsAnchor(): ?int
    {
        return $this->isAnchor;
    }

    /**
     * Set category is anchor
     *
     * @param int|null $isAnchor
     *
     * @return void
     */
    public function setIsAnchor(?int $isAnchor): void
    {
        $this->isAnchor = $isAnchor;
    }

    /**
     * Get category display mode
     *
     * @return string|null
     */
    public function getDisplayMode(): ?string
    {
        return $this->displayMode;
    }

    /**
     * Set category display mode
     *
     * @param string|null $displayMode
     *
     * @return void
     */
    public function setDisplayMode(?string $displayMode): void
    {
        $this->displayMode = $displayMode;
    }

    /**
     * Get category available sort by options
     *
     * @return string[]|null
     */
    public function getAvailableSortBy(): ?array
    {
        return $this->availableSortBy;
    }

    /**
     * Set category available sort by options
     *
     * @param string[]|null $availableSortBy
     *
     * @return void
     */
    public function setAvailableSortBy(?array $availableSortBy): void
    {
        $this->availableSortBy = $availableSortBy;
    }

    /**
     * Get category meta title
     *
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    /**
     * Set category meta title
     *
     * @param string|null $metaTitle
     *
     * @return void
     */
    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * Get category meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    /**
     * Set category meta keywords
     *
     * @param string|null $metaKeywords
     *
     * @return void
     */
    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * Get category meta description
     *
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * Set category meta description
     *
     * @param string|null $metaDescription
     *
     * @return void
     */
    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return int
     */
    public function getIsActive(): int
    {
        return $this->isActive;
    }

    /**
     * @param int $isActive
     * @return void
     */
    public function setIsActive(int $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return int|null
     */
    public function getIncludeInMenu(): ?int
    {
        return $this->includeInMenu;
    }

    /**
     * @param int|null $includeInMenu
     * @return void
     */
    public function setIncludeInMenu(?int $includeInMenu): void
    {
        $this->includeInMenu = $includeInMenu;
    }
}
