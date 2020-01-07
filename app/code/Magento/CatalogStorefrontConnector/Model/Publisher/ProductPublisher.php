<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\Framework\App\State;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Push product data for given product ids and store id to the Message Bus with topic
 * storefront.collect.update.entities.data
 */
class ProductPublisher
{
    /**
     * @var DataProviderInterface
     */
    private $productsDataProvider;

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
     * @param DataProviderInterface $productsDataProvider
     * @param EntitiesUpdateMessageBuilder $messageBuilder
     * @param PublisherInterface $queuePublisher
     * @param State $state
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $productsDataProvider,
        EntitiesUpdateMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        State $state,
        int $batchSize
    ) {
        $this->productsDataProvider = $productsDataProvider;
        $this->messageBuilder = $messageBuilder;
        $this->queuePublisher = $queuePublisher;
        $this->batchSize = $batchSize;
        $this->state = $state;
    }

    /**
     * Publish new messages to storefront.collect.update.entities.data topic
     *
     * @param array $productIds
     * @param int $storeId
     * @return void
     * @throws \Exception
     */
    public function publish(array $productIds, int $storeId): void
    {
        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            function () use ($productIds, $storeId) {
                foreach (\array_chunk($productIds, $this->batchSize) as $idsBunch) {
                    $messages = [];
                    $productsData = $this->productsDataProvider->fetch($idsBunch, [], ['store' => $storeId]);
                    foreach ($productsData as $product) {
                        $messages[] = $this->messageBuilder->build(
                            $storeId,
                            'product',
                            (int)$product['entity_id'],
                            $product
                        );
                    }
                    $this->queuePublisher->publish(self::TOPIC_NAME, $messages);
                }
            }
        );
    }
}
