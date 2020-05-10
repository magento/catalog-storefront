<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CategoryExtractor\DataProvider\DataProviderInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Category publisher
 *
 * Push product data for given category ids and store id to the Message Bus
 * with topic storefront.catalog.data.consume
 */
class CategoryPublisher
{
    private const ROOT_CATEGORY_ID = 1;

    /**
     * @var DataProviderInterface
     */
    private $categoriesDataProvider;

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
     * @param DataProviderInterface $categoriesDataProvider
     * @param CatalogItemMessageBuilder $messageBuilder
     * @param PublisherInterface $queuePublisher
     * @param State $state
     * @param LoggerInterface $logger
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $categoriesDataProvider,
        CatalogItemMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        State $state,
        LoggerInterface $logger,
        int $batchSize
    ) {
        $this->categoriesDataProvider = $categoriesDataProvider;
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
     * @param array $categoryIds
     * @param int $storeId
     * @return void
     * @throws \Exception
     */
    public function publish(string $eventType, array $categoryIds, int $storeId): void
    {
        $this->state->emulateAreaCode(
            Area::AREA_FRONTEND,
            function () use ($eventType, $categoryIds, $storeId) {
                try {
                    $this->publishEntities($eventType, $categoryIds, $storeId);
                } catch (\Throwable $e) {
                    $this->logger->critical(
                        \sprintf(
                            'Error on publish category ids "%s" in store %s',
                            \implode(', ', $categoryIds),
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
     * @param array $categoryIds
     * @param int $storeId
     * @return void
     */
    private function publishEntities(string $eventType, array $categoryIds, int $storeId): void
    {
        foreach (\array_chunk($categoryIds, $this->batchSize) as $idsBunch) {
            $messages = [];
            $categoriesData = $this->categoriesDataProvider->fetch($idsBunch, [], ['store' => $storeId]);
            $this->logger->debug(
                \sprintf('Publish category with ids "%s" in store %s', \implode(', ', $categoryIds), $storeId),
                ['verbose' => $categoriesData]
            );
            foreach ($categoryIds as $categoryId) {
                if ($categoryId === self::ROOT_CATEGORY_ID) {
                    continue;
                }
                $category = isset($categoriesData[$categoryId]['id']) ? $categoriesData[$categoryId] :[];
                $messages[] = $this->messageBuilder->build(
                    $eventType,
                    $storeId,
                    'category',
                    $categoryId,
                    $category
                );
            }
            if (!empty($messages)) {
                $this->queuePublisher->publish(self::TOPIC_NAME, $messages);
            }
        }
    }
}
