<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Product publisher
 *
 * Push product data for given product ids and store id to the Message Bus
 * with topic storefront.catalog.data.consume
 */
class ProductPublisher
{
    /**
     * @var DataProviderInterface
     */
    private $productsDataProvider;

    /**
     * @var CatalogItemMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var string
     */
    private const TOPIC_NAME = 'storefront.catalog.data.consume';

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DataProviderInterface $productsDataProvider
     * @param CatalogItemMessageBuilder $messageBuilder
     * @param PublisherInterface $queuePublisher
     * @param State $state
     * @param LoggerInterface $logger
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $productsDataProvider,
        CatalogItemMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        State $state,
        LoggerInterface $logger,
        int $batchSize
    ) {
        $this->productsDataProvider = $productsDataProvider;
        $this->messageBuilder = $messageBuilder;
        $this->queuePublisher = $queuePublisher;
        $this->batchSize = $batchSize;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * Publish new messages to storefront.catalog.data.consume topic
     *
     * @param string $eventType
     * @param array $productIds
     * @param int $storeId
     * @return void
     * @throws \Exception
     */
    public function publish(string $eventType, array $productIds, int $storeId): void
    {
        $this->state->emulateAreaCode(
            Area::AREA_FRONTEND,
            function () use ($eventType, $productIds, $storeId) {
                try {
                    $this->publishEntities($eventType, $productIds, $storeId);
                } catch (\Throwable $e) {
                    $this->logger->critical(
                        \sprintf(
                            'Error on publish product ids "%s" in store %s',
                            \implode(', ', $productIds),
                            $storeId
                        ),
                        ['exception' => $e]
                    );
                }
            }
        );
    }

    /**
     * Publish entities to the queue
     *
     * @param string $eventType
     * @param array $productIds
     * @param int $storeId
     * @return void
     */
    private function publishEntities(string $eventType, array $productIds, int $storeId): void
    {
        foreach (\array_chunk($productIds, $this->batchSize) as $idsBunch) {
            $messages = [];
            $productsData = $this->productsDataProvider->fetch($idsBunch, [], ['store' => $storeId]);
            $this->logger->debug(
                \sprintf('Publish products with ids "%s" in store %s', \implode(', ', $productIds), $storeId),
                ['verbose' => $productsData]
            );
            foreach ($idsBunch as $productId) {
                $product = $productsData[$productId] ?? [];
                $messages[] = $this->messageBuilder->build(
                    $eventType,
                    $storeId,
                    'product',
                    $productId,
                    $product
                );
            }
            if (!empty($messages)) {
                $this->queuePublisher->publish(self::TOPIC_NAME, $messages);
            }
        }
    }
}
