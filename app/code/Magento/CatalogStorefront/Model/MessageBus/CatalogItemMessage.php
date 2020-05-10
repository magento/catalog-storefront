<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\MessageBus;

/**
 * DTO represent catalog item data stored in Message Bus
 */
class CatalogItemMessage
{
    /**
     * @var string
     */
    protected $eventType;

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
     * @var array
     */
    private $entityData;

    /**
     * @param string $eventType
     * @param string $entityType
     * @param int $entityId
     * @param int $storeId
     * @param array $entityData
     */
    public function __construct(string $eventType, string $entityType, int $entityId, int $storeId, array $entityData)
    {
        $this->eventType = $eventType;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->storeId = $storeId;
        $this->entityData = $entityData;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @param string $eventType
     */
    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
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
     * @return array
     */
    public function getEntityData(): array
    {
        return $this->entityData;
    }
}
