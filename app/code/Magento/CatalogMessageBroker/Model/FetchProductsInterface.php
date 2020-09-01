<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventData;

/**
 * Fetch product data
 */
interface FetchProductsInterface
{
    /**
     * Fetch product data
     *
     * @param EventData $eventData
     *
     * @return array
     */
    public function execute(EventData $eventData): array;
}
