<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontMessageBus\Message;

/**
 * DTO to transfer product/category data between different systems.
 *
 * Used by Product Information Manager system, which collect entity updates and put message into the queue.
 * Catalog Storefront consume message from queue
 */
class CatalogItem
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
     * @param string $entity_type
     * @param int $entity_id
     * @param int $store_id
     * @param string $entity_data
     * @see \Magento\Framework\Webapi\ServiceInputProcessor::process for exalanation snake_case argument naming
     */
    public function __construct(string $entity_type, int $entity_id, int $store_id, string $entity_data)
    {
        $this->entityType = $entity_type;
        $this->entityId = $entity_id;
        $this->storeId = $store_id;
        $this->entityData = $entity_data;
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
