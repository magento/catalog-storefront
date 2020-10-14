<?php
# Generated by the Magento PHP proto generator.  DO NOT EDIT!

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Autogenerated description for ProductReviewResponse class
 *
 * phpcs:disable Magento2.PHP.FinalImplementation
 * @SuppressWarnings(PHPMD)
 * @SuppressWarnings(PHPCPD)
 */
final class ProductReviewResponse implements ProductReviewResponseInterface
{

    /**
     * @var array
     */
    private $items;

    /**
     * @var \Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface
     */
    private $pagination;
    
    /**
     * @inheritdoc
     *
     * @return \Magento\CatalogStorefrontApi\Api\Data\ReadReviewInterface[]
     */
    public function getItems(): array
    {
        return (array) $this->items;
    }
    
    /**
     * @inheritdoc
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\ReadReviewInterface[] $value
     * @return void
     */
    public function setItems(array $value): void
    {
        $this->items = $value;
    }
    
    /**
     * @inheritdoc
     *
     * @return \Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface|null
     */
    public function getPagination(): ?\Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface
    {
        return $this->pagination;
    }
    
    /**
     * @inheritdoc
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface $value
     * @return void
     */
    public function setPagination(\Magento\CatalogStorefrontApi\Api\Data\PaginationResponseInterface $value): void
    {
        $this->pagination = $value;
    }
}
