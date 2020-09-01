<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Category;

use Magento\CatalogExport\Model\Data\ChangedEntitiesInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterfaceFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventDataBuilder;
use Psr\Log\LoggerInterface;

/**
 * Process categories update messages and update storefront app
 */
class CategoriesConsumer
{
    /**
     * Event types to handle incoming messages from Export API
     * TODO: make private after https://github.com/magento/catalog-storefront/issues/242
     */
    const CATEGORIES_UPDATED_EVENT_TYPE = 'categories_updated';

    const CATEGORIES_DELETED_EVENT_TYPE = 'categories_deleted';

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
    public function processMessage(ChangedEntitiesInterface $message)
    {
        try {
            $categoriesEvent = $this->consumerEventFactory->create(
                $message->getMeta() ? $message->getMeta()->getEventType() : null
            );
            $categoriesEvent->execute($this->eventDataBuilder->execute($message));
        } catch (\Throwable $e) {
            $this->logger->critical('Unable to process collected category data for update/delete. ' . $e->getMessage());
        }
    }
}
