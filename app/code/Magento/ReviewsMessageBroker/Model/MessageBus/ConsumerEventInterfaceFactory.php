<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsMessageBroker\Model\MessageBus;

use Magento\Framework\ObjectManagerInterface;

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
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $eventTypeMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $eventTypeMap
    ) {
        $this->objectManager = $objectManager;
        $this->eventTypeMap = $eventTypeMap;
    }

    /**
     * Create consumer event according to specified event type
     *
     * @param string $eventType
     *
     * @return ConsumerEventInterface
     *
     * @throws \InvalidArgumentException
     */
    public function create(string $eventType)
    {
        if (isset($this->eventTypeMap[$eventType])) {
            return $this->objectManager->create($this->eventTypeMap[$eventType]);
        }

        throw new \InvalidArgumentException(\sprintf('The provided event type "%s" was not recognized', $eventType));
    }
}
