<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

use Magento\CatalogExport\Model\Data\ChangedEntitiesInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterfaceFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventDataBuilder;
use Psr\Log\LoggerInterface;

/**
 * Process product update messages and update storefront app
 */
class ProductsConsumer
{
    /**
     * Event types to handle incoming messages from Export API
     * TODO: make private after https://github.com/magento/catalog-storefront/issues/242
     */
    const PRODUCTS_UPDATED_EVENT_TYPE = 'products_updated';

    const PRODUCTS_DELETED_EVENT_TYPE = 'products_deleted';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConsumerEventInterfaceFactory
     */
    private $consumerEventFactory;

    /**
     * @var EventDataBuilder
     */
    private $eventDataBuilder;

    /**
     * @param LoggerInterface $logger
     * @param ConsumerEventInterfaceFactory $consumerEventFactory
     * @param EventDataBuilder $eventDataBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        ConsumerEventInterfaceFactory $consumerEventFactory,
        EventDataBuilder $eventDataBuilder
    ) {
        $this->logger = $logger;
        $this->consumerEventFactory = $consumerEventFactory;
        $this->eventDataBuilder = $eventDataBuilder;
    }

    /**
     * Process message
     *
     * @param \Magento\CatalogExport\Model\Data\ChangedEntitiesInterface $message
     * @return void
     */
    public function processMessage(ChangedEntitiesInterface $message): void
    {
        try {
            $productsEvent = $this->consumerEventFactory->create(
                $message->getMeta() ? $message->getMeta()->getEventType() : null
            );
            $productsEvent->execute($this->eventDataBuilder->execute($message));
        } catch (\Throwable $e) {
            $this->logger->critical('Unable to process collected product data for update/delete. ' . $e->getMessage());
        }
    }
}
