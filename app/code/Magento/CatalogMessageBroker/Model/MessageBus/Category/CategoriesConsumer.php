<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Category;

use Magento\CatalogExport\Model\Data\ChangedEntitiesInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterfaceFactory;
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
     * @param LoggerInterface $logger
     * @param ConsumerEventInterfaceFactory $consumerEventFactory
     */
    public function __construct(
        LoggerInterface $logger,
        ConsumerEventInterfaceFactory $consumerEventFactory
    ) {
        $this->logger = $logger;
        $this->consumerEventFactory = $consumerEventFactory;
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
            $eventType = $message->getMeta() ? $message->getMeta()->getEventType() : null;
            $scope = $message->getMeta() ? $message->getMeta()->getScope() : null;
            $entityIds = $message->getData() ? $message->getData()->getIds() : null;
            if (empty($entityIds)) {
                throw new \InvalidArgumentException('Category Ids are missing in payload');
            }
            $categoriesEvent = $this->consumerEventFactory->create($eventType);
            $categoriesEvent->execute($entityIds, $scope);
        } catch (\Throwable $e) {
            $this->logger->critical('Unable to process collected category data for update/delete. ' . $e->getMessage());
        }
    }
}
