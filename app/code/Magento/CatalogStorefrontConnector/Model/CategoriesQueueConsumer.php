<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogDataExporter\Model\Feed\Categories as CategoriesFeed;
use Magento\CatalogDataExporter\Model\Indexer\CategoryFeedIndexer;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer;
use Magento\CatalogStorefrontConnector\Helper\CustomStoreResolver;
use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
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
     * @var CategoryFeedIndexer
     */
    private $categoryFeedIndexer;

    /**
     * @var CategoriesFeed
     */
    private $categoriesFeed;

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
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param CategoriesConsumer $categoriesConsumer
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     * @param CustomStoreResolver $storeResolver
     * @param LoggerInterface $logger
     * @param CategoriesFeed $categoriesFeed
     * @param CategoryFeedIndexer $categoryFeedIndexer
     */
    public function __construct(
        ChangedEntitiesMessageBuilder $messageBuilder,
        CategoriesConsumer $categoriesConsumer,
        CatalogEntityIdsProvider $catalogEntityIdsProvider,
        CustomStoreResolver $storeResolver,
        LoggerInterface $logger,
        CategoriesFeed $categoriesFeed,
        CategoryFeedIndexer $categoryFeedIndexer
    ) {
        $this->categoriesConsumer = $categoriesConsumer;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->categoryFeedIndexer = $categoryFeedIndexer;
        $this->categoriesFeed = $categoriesFeed;
        $this->messageBuilder = $messageBuilder;
        $this->logger = $logger;
        $this->storeResolver = $storeResolver;
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
                foreach ($this->catalogEntityIdsProvider->getCategoryIds($storeId) as $idsChunk) {
                    $ids[] = $idsChunk;
                }
            }
            //TODO: move these reindexes to plugins to avoid calling them per store view?
            $this->categoryFeedIndexer->executeList($ids);

            $deletedIds = [];
            foreach ($this->categoriesFeed->getDeletedByIds($ids, array_filter([$storeCode])) as $category) {
                $deletedIds[] = $category['categoryId'];
                unset($ids[$category['categoryId']]);
            }

            if (!empty($ids)) {
                $this->passMessage(
                    CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE,
                    $ids,
                    $storeCode
                );
            }

            if (!empty($deletedIds)) {
                $this->passMessage(
                    CategoriesConsumer::CATEGORIES_DELETED_EVENT_TYPE,
                    $deletedIds,
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
     * @param int[] $ids
     * @param string $storeCode
     */
    private function passMessage(string $eventType, array $ids, string $storeCode)
    {
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            $message = $this->messageBuilder->build(
                $idsChunk,
                $eventType,
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
