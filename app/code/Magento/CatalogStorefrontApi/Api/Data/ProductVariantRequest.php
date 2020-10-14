<?php
# Generated by the Magento PHP proto generator.  DO NOT EDIT!

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Autogenerated description for ProductVariantRequest class
 *
 * phpcs:disable Magento2.PHP.FinalImplementation
 * @SuppressWarnings(PHPMD)
 * @SuppressWarnings(PHPCPD)
 */
final class ProductVariantRequest implements ProductVariantRequestInterface
{

    /**
     * @var string
     */
    private $productId;

    /**
     * @var string
     */
    private $store;

    /**
     * @var array
     */
    private $pagination;
    
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getProductId(): string
    {
        return (string) $this->productId;
    }
    
    /**
     * @inheritdoc
     *
     * @param string $value
     * @return void
     */
    public function setProductId(string $value): void
    {
        $this->productId = $value;
    }
    
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getStore(): string
    {
        return (string) $this->store;
    }
    
    /**
     * @inheritdoc
     *
     * @param string $value
     * @return void
     */
    public function setStore(string $value): void
    {
        $this->store = $value;
    }
    
    /**
     * @inheritdoc
     *
     * @return \Magento\CatalogStorefrontApi\Api\Data\PaginationRequestInterface[]
     */
    public function getPagination(): array
    {
        return (array) $this->pagination;
    }
    
    /**
     * @inheritdoc
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\PaginationRequestInterface[] $value
     * @return void
     */
    public function setPagination(array $value): void
    {
        $this->pagination = $value;
    }
}
