<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

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

    /**
     * TODO: ad-hoc Remove this once the store scope is consistently passed from ExportAPI
     */
    const DEFAULT_STORE_VIEW = 'default';

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
    public function processMessage($message): void
    {
        /** @var \Magento\CatalogMessageBroker\Model\MessageBus\Data\ChangedEntitiesInterface $message */
        try {
            $eventType = $message->getMeta() ? $message->getMeta()->getEventType() : null;
            $scope = $message->getMeta() ? $message->getMeta()->getScope() ?? self::DEFAULT_STORE_VIEW : null;
            $entityIds = $message->getData() ? $message->getData()->getIds() : null;
            if (empty($entityIds)) {
                throw new \InvalidArgumentException('Product ids are missing in payload');
            }
            $productsEvent = $this->consumerEventFactory->create($eventType);
            $productsEvent->execute($entityIds, $scope);
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Unable to process collected product data. Event type: "%s", ids:  "%s"',
                    $eventType ?? '',
                    \implode(',', $entityIds ?? [])
                ),
                ['exception' => $e]
            );
        }
    }
}
