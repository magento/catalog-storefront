<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogDataExporter\Model\Indexer\CategoryFeedIndexer;
use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer;
use Magento\CatalogDataExporter\Model\Feed\Categories as CategoriesFeed;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Consumer processes messages with store front categories data
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
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param CategoriesConsumer $categoriesConsumer
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param CategoriesFeed $categoriesFeed
     * @param CategoryFeedIndexer $categoryFeedIndexer
     */
    public function __construct(
        ChangedEntitiesMessageBuilder $messageBuilder,
        CategoriesConsumer $categoriesConsumer,
        CatalogEntityIdsProvider $catalogEntityIdsProvider,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        CategoriesFeed $categoriesFeed,
        CategoryFeedIndexer $categoryFeedIndexer
    ) {
        $this->categoriesConsumer = $categoriesConsumer;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->categoryFeedIndexer = $categoryFeedIndexer;
        $this->categoriesFeed = $categoriesFeed;
        $this->messageBuilder = $messageBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Process collected category IDs for update
     * TODO: Eliminate the redundant calls. The incoming message is storeId specific.
     *
     * Process messages from storefront.collect.updated.categories.data
     *
     * @param UpdatedEntitiesDataInterface $message
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated React on events triggered by plugins to push data to SF storage
     */
    public function processMessages(UpdatedEntitiesDataInterface $message): void
    {
        $incomingStoreId = $message->getStoreId();
        $ids = $message->getEntityIds();

        if (empty($ids)) {
            foreach ($this->catalogEntityIdsProvider->getCategoryIds($incomingStoreId) as $idsChunk) {
                $ids[] = $idsChunk;
            }
        }
        $this->categoryFeedIndexer->executeList($ids);

        $storesToIds = $this->getMappedStores();
        $deleted = [];
        foreach ($this->categoriesFeed->getDeletedByIds($ids) as $category) {
            $storeId = $this->resolveStoreId($storesToIds, $category['storeViewCode']);
            $deleted[$storeId][] = $category['categoryId'];
            unset($ids[$category['categoryId']]);
        }

        foreach ($deleted as $storeId => $entityIds) {
            foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                if (!empty($idsChunk) && $storeId === $incomingStoreId) {
                    $this->passMessage(
                        CategoriesConsumer::CATEGORIES_DELETED_EVENT_TYPE,
                        $idsChunk,
                        (string)$storeId
                    );
                }
            }
        }
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                $this->passMessage(
                    CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE,
                    $idsChunk,
                );
            }
        }
    }

    /**
     * Publish deleted or updated message
     *
     * @param string $eventType
     * @param int[] $ids
     * @param null|string $scope
     */
    private function passMessage(string $eventType, array $ids, ?string $scope = null)
    {
        $message = $this->messageBuilder->build(
            $ids,
            $eventType,
            $scope
        );
        try {
            $this->categoriesConsumer->processMessage($message);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Resolve store ID by store code
     *
     * @param array $mappedStores
     * @param string $storeCode
     * @return string|mixed
     */
    private function resolveStoreId(array $mappedStores, string $storeCode)
    {
        //workaround for tests
        return $mappedStores[$storeCode] ?? '1';
    }

    /**
     * Retrieve mapped stores, in case if something went wrong, retrieve just one default store
     *
     * @return array
     */
    private function getMappedStores(): array
    {
        try {
            // @todo eliminate store manager
            $stores = $this->storeManager->getStores(true);
            $storesToIds = [];
            foreach ($stores as $store) {
                $storesToIds[$store->getCode()] = (string)$store->getId();
            }
        } catch (\Throwable $e) {
            $storesToIds['default'] = '1';
        }

        return $storesToIds;
    }

}
