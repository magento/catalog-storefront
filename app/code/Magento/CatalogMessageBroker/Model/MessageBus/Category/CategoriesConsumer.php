<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Category;

use Magento\CatalogMessageBroker\Model\MessageBus\Data\ExportMessageInterface;
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

    //TODO: ad-hoc Remove once the store codes are consistently passed in the messages coming from ExportApi
    const DEFAULT_STORE_CODE = 'default';

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
     * @param $message
     * @return void
     */
    public function processMessage($message)
    {
        try {
            $eventType = $message->getMeta() ? $message->getMeta()->getEventType() : null;
            //TODO: ad-hoc Remove DEFAULT_STORE_CODE once the store codes are passed consistently
            $scope = $message->getMeta() ? $message->getMeta()->getScope() ?? self::DEFAULT_STORE_CODE : null;
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
