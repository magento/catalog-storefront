<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventData;

/**
 * Execute consumer event
 */
interface ConsumerEventInterface
{
    /**
     * Execute consumers by ids for specified scope
     *
     * @param EventData $eventData
     *
     * @return void
     */
    public function execute(EventData $eventData): void;
}
