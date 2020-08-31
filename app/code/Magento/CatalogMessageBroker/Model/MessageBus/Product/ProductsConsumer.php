<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

use Magento\CatalogMessageBroker\Model\MessageBus\Data\ExportMessageInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterfaceFactory;
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

    //TODO: ad-hoc Remove once the store codes are passed in the messages coming from ExportApi
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
     * @param \Magento\CatalogMessageBroker\Model\MessageBus\Data\ExportMessageInterface $message
     * @return void
     */
    public function processMessage($message): void
    {
        try {
            $eventType = $message->getMeta() ? $message->getMeta()->getEventType() : null;

            //TODO: ad-hoc Remove DEFAULT_STORE_CODE once the store codes are passed consistently
            $scope = $message->getMeta() ? $message->getMeta()->getScope() ?? self::DEFAULT_STORE_CODE : null;
            $entityIds = $message->getData() ? $message->getData()->getIds() : null;
            if (empty($entityIds)) {
                throw new \InvalidArgumentException('Product ids are missing in payload');
            }
            if (empty($scope)) {
                throw new \InvalidArgumentException('Scope is missing from the payload');
            }
            $productsEvent = $this->consumerEventFactory->create($eventType);
            $productsEvent->execute($entityIds, $scope);
        } catch (\Throwable $e) {
            $this->logger->critical('Unable to process collected product data for update/delete. ' . $e->getMessage());
        }
    }
}
