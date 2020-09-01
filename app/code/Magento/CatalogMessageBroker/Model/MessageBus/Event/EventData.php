<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Event;

/**
 * Event data class
 */
class EventData
{
    /**
     * @var string
     */
    private $scope;

    /**
     * @var EventEntityData[]
     */
    private $entities;

    /**
     * @param string $scope
     * @param EventEntityData[] $entities
     */
    public function __construct(string $scope, array $entities)
    {
        $this->scope = $scope;
        $this->entities = $entities;
    }

    /**
     * Get event scope
     *
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * Get entities
     *
     * @return \Magento\CatalogMessageBroker\Model\MessageBus\Event\EventEntityData[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }
}
