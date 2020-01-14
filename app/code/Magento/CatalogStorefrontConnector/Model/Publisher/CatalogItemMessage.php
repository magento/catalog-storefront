<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

/**
 * DTO to transfer product/category data from Magento Admin to Catalog Storefront service via message bus
 */
class CatalogItemMessage
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var int
     */
    private $entityId;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var string
     */
    private $entityData;

    /**
     * @param string $entityType
     * @param int $entityId
     * @param int $storeId
     * @param string $entityData
     */
    public function __construct(string $entityType, int $entityId, int $storeId, string $entityData)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->storeId = $storeId;
        $this->entityData = $entityData;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * Get entity ID
     *
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->storeId;
    }

    /**
     * Get entity data
     *
     * @return string
     */
    public function getEntityData(): string
    {
        return $this->entityData;
    }
}
