<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Event;

use Magento\CatalogExport\Model\Data\ChangedEntitiesInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventDataFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventEntityDataFactory;

/**
 * Class responsible for building event data
 */
class EventDataBuilder
{
    /**
     * @var EventDataFactory
     */
    private $eventDataFactory;

    /**
     * @var EventEntityDataFactory
     */
    private $eventEntityDataFactory;

    /**
     * @var array
     */
    private $requiredAttributes;

    /**
     * @param EventDataFactory $eventDataFactory
     * @param EventEntityDataFactory $eventEntityDataFactory
     * @param array $requiredAttributes
     */
    public function __construct(
        EventDataFactory $eventDataFactory,
        EventEntityDataFactory $eventEntityDataFactory,
        array $requiredAttributes = []
    ) {
        $this->eventDataFactory = $eventDataFactory;
        $this->eventEntityDataFactory = $eventEntityDataFactory;
        $this->requiredAttributes = $requiredAttributes;
    }

    /**
     * Execute event data builder
     *
     * @param ChangedEntitiesInterface $message
     *
     * @return EventData
     *
     * @throws \InvalidArgumentException
     */
    public function execute(ChangedEntitiesInterface $message): EventData
    {
        $entities = $message->getData() ? $message->getData()->getEntities() : null;

        if (empty($entities)) {
            throw new \InvalidArgumentException('Entity data is missing in payload');
        }

        // TODO make merging requiredAttributes more flexible.
        // Each entity contains the same set of required attributes
        $productsData = [];
        foreach ($entities as $entity) {
            $productsData[$entity->getEntityId()] = $this->eventEntityDataFactory->create([
                'entityId' => $entity->getEntityId(),
                'attributes' => !empty($entity->getAttributes()) ? \array_unique(\array_merge(
                    $entity->getAttributes(),
                    \array_values($this->requiredAttributes)
                )) : [],
            ]);
        }

        return $this->eventDataFactory->create([
            'scope' => $message->getMeta() ? $message->getMeta()->getScope() : null,
            'entities' => $productsData,
        ]);
    }
}
