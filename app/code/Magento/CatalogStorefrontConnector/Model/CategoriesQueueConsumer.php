<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\Category\CategoriesConsumer;
use Magento\CatalogStorefrontConnector\Helper\CustomStoreResolver;
use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\DataExporter\Model\Indexer\FeedIndexer;
use Magento\DataExporter\Model\FeedPool;
use Psr\Log\LoggerInterface;

/**
 * Consumer processes messages with store front categories data
 * @deprecared https://github.com/magento/catalog-storefront/issues/242
 */
class CategoriesQueueConsumer
{
    const BATCH_SIZE = 100;

    /**
     * @var CategoriesConsumer
     */
    private $categoriesConsumer;

    /**
     * @var CatalogEntityIdsProvider
     */
    private $catalogEntityIdsProvider;

    /**
     * @var FeedIndexer
     */
    private $categoryFeedIndexer;

    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomStoreResolver
     */
    private $storeResolver;

    /**
     * @var FeedPool
     */
    private $feedPool;

    /**
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param CategoriesConsumer $categoriesConsumer
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     * @param CustomStoreResolver $storeResolver
     * @param LoggerInterface $logger
     * @param FeedPool $feedPool
     * @param FeedIndexer $categoryFeedIndexer
     */
    public function __construct(
        ChangedEntitiesMessageBuilder $messageBuilder,
        CategoriesConsumer $categoriesConsumer,
        CatalogEntityIdsProvider $catalogEntityIdsProvider,
        CustomStoreResolver $storeResolver,
        LoggerInterface $logger,
        FeedPool $feedPool,
        FeedIndexer $categoryFeedIndexer
    ) {
        $this->categoriesConsumer = $categoriesConsumer;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->categoryFeedIndexer = $categoryFeedIndexer;
        $this->messageBuilder = $messageBuilder;
        $this->logger = $logger;
        $this->storeResolver = $storeResolver;
        $this->feedPool = $feedPool;
    }

    /**
     * Process collected category IDs for update/delete
     *
     * @param UpdatedEntitiesDataInterface $message
     * @return void
     * @deprecated React on events triggered by plugins to push data to SF storage
     */
    public function processMessages(UpdatedEntitiesDataInterface $message): void
    {
        try {
            $storeId = $message->getStoreId();
            $storeCode = $this->storeResolver->resolveStoreCode($storeId);
            $ids = $message->getEntityIds();

            //TODO: remove ad-hoc solution after moving events to corresponding export service
            if (empty($ids)) {
                $this->categoryFeedIndexer->executeFull();
                foreach ($this->catalogEntityIdsProvider->getCategoryIds($storeId) as $idsChunk) {
                    $ids = \array_merge($ids, $idsChunk);
                }
            } else {
                //TODO: move these reindexes to plugins to avoid calling them per store view?
                $this->categoryFeedIndexer->executeList($ids);
            }

            $deletedIds = [];
            $categoriesFeed = $this->feedPool->getFeed('categories');
            foreach ($categoriesFeed->getDeletedByIds($ids, array_filter([$storeCode])) as $category) {
                $deletedIds[] = $category['categoryId'];
                unset($ids[$category['categoryId']]);
            }

            $categoriesArray = [];
            foreach ($ids as $id) {
                $categoriesArray[] = [
                    'entity_id' => (int)$id,
                ];
            }

            $deletedArray = [];
            foreach ($deletedIds as $id) {
                $deletedArray[] = [
                    'entity_id' => (int)$id,
                ];
            }

            if (!empty($categoriesArray)) {
                $this->passMessage(
                    CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE,
                    $categoriesArray,
                    $storeCode
                );
            }

            if (!empty($deletedArray)) {
                $this->passMessage(
                    CategoriesConsumer::CATEGORIES_DELETED_EVENT_TYPE,
                    $deletedArray,
                    $storeCode
                );
            }
        } catch (\Throwable $e) {
            $this->logger->critical('Unable to process collected category data for update/delete. ' . $e->getMessage());
        }
    }

    /**
     * Publish deleted or updated message
     *
     * @param string $eventType
     * @param array $products
     * @param string $storeCode
     */
    private function passMessage(string $eventType, array $products, string $storeCode)
    {
        foreach (array_chunk($products, self::BATCH_SIZE) as $chunk) {
            $message = $this->messageBuilder->build(
                $eventType,
                $chunk,
                $storeCode
            );
            try {
                $this->categoriesConsumer->processMessage($message);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
