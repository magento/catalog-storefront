<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus;

/**
 * Factory for creating consumer event
 */
class ConsumerEventInterfaceFactory
{
    /**
     * @var array
     */
    private $eventTypeMap;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $eventTypeMap
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $eventTypeMap
    ) {
        $this->objectManager = $objectManager;
        $this->eventTypeMap = $eventTypeMap;
    }

    /**
     * Create consumer event according to specified event type
     *
     * @param string $eventType
     * @return \Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface
     */
    public function create(string $eventType)
    {
        if (isset($this->eventTypeMap[$eventType])) {
            return $this->objectManager->create($this->eventTypeMap[$eventType]);
        }
        throw new \InvalidArgumentException(
            \sprintf(
                'The provided event type "%s" was not recognized',
                $eventType
            )
        );
    }
}
