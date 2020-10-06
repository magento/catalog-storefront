<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

use Magento\CatalogExport\Event\Data\ChangedEntities;
use Magento\CatalogExport\Event\Data\Entity;
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
     * @param ChangedEntities $message
     * @return void
     */
    public function processMessage(ChangedEntities $message): void
    {
        try {
            $eventType = $message->getMeta() ? $message->getMeta()->getEventType() : null;
            $entities = $message->getData() ? $message->getData()->getEntities() : null;

            if (empty($entities)) {
                throw new \InvalidArgumentException('Products data is missing in payload');
            }

            $productsEvent = $this->consumerEventFactory->create($eventType);
            $productsEvent->execute($entities, $message->getMeta()->getScope());
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Unable to process collected product data. Event type: "%s", ids:  "%s"',
                    $eventType ?? '',
                    \implode(',', \array_map(function (Entity $entity) {
                        return $entity->getEntityId();
                    }, $entities ?? []))
                ),
                ['exception' => $e]
            );
        }
    }
}
