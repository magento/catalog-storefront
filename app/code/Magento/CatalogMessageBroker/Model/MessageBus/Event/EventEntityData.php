<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Event;

/**
 * Event entity data class
 */
class EventEntityData
{
    /**
     * @var int
     */
    private $entityId;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param int $entityId
     * @param array $attributes
     */
    public function __construct(int $entityId, array $attributes)
    {
        $this->entityId = $entityId;
        $this->attributes = $attributes;
    }

    /**
     * Get entity id
     *
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * Get product attributes
     *
     * @return string[]
     */
    public function getAttributes(): array
    {
        return (array)$this->attributes;
    }
}
