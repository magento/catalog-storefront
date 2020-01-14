<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CatalogCategory\DataProvider\DataProviderInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Category publisher
 *
 * Push product data for given category ids and store id to the Message Bus
 * with topic storefront.collect.update.entities.data
 */
class CategoryPublisher
{
    /**
     * @var DataProviderInterface
     */
    private $categoriesDataProvider;

    /**
     * @var EntitiesUpdateMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var string
     */
    private const TOPIC_NAME = 'storefront.collect.update.entities.data';

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var State
     */
    private $state;

    /**
     * @param DataProviderInterface $categoriesDataProvider
     * @param EntitiesUpdateMessageBuilder $messageBuilder
     * @param PublisherInterface $queuePublisher
     * @param State $state
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $categoriesDataProvider,
        EntitiesUpdateMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        State $state,
        int $batchSize
    ) {
        $this->categoriesDataProvider = $categoriesDataProvider;
        $this->messageBuilder = $messageBuilder;
        $this->queuePublisher = $queuePublisher;
        $this->batchSize = $batchSize;
        $this->state = $state;
    }

    /**
     * Publish new messages to storefront.collect.update.entities.data topic
     *
     * @param array $categoryIds
     * @param int $storeId
     * @return void
     * @throws \Exception
     */
    public function publish(array $categoryIds, int $storeId): void
    {

        $this->state->emulateAreaCode(
            Area::AREA_FRONTEND,
            function () use ($categoryIds, $storeId) {
                foreach (\array_chunk($categoryIds, $this->batchSize) as $idsBunch) {
                    $messages = [];
                    $categoriesData = $this->categoriesDataProvider->fetch($idsBunch, [], ['store' => $storeId]);
                        foreach ($categoriesData as $category) {
                        $messages[] = $this->messageBuilder->build(
                            $storeId,
                            'category',
                            (int)$category['entity_id'],
                            $category
                        );
                    }
                    if (!empty($messages)) {
                        $this->queuePublisher->publish(self::TOPIC_NAME, $messages);
                    }
                }
            }
        );
    }
}
