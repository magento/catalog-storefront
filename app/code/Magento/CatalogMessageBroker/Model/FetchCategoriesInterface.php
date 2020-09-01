<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventData;

/**
 * Fetch categories data
 */
interface FetchCategoriesInterface
{
    /**
     * Fetch categories data
     *
     * @param EventData $eventData
     *
     * @return array
     */
    public function execute(EventData $eventData): array;
}
