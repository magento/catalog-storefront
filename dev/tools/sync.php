<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Ad-hoc solution to quickly sync data without AMQP or CRON enabled
 */
use Magento\CatalogMessageBroker\Model\MessageBus\Category\CategoriesConsumer;
use Magento\CatalogMessageBroker\Model\MessageBus\Product\ProductsConsumer;

try {
    require __DIR__ . '/app/bootstrap.php';
} catch (\Exception $e) {
    exit(1);
}

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$om = $bootstrap->getObjectManager();
$batchSize = 3000;

/**
 * @param string $feedTable
 * @param int $batchSize
 * @param int $offset
 * @return array
 */
function getBatchIds(string $feedTable, int $batchSize, int $offset): array
{
    global $om;
    /** @var \Magento\Framework\App\ResourceConnection $connection */
    $connection = $om->get(\Magento\Framework\App\ResourceConnection::class);
    $select = $connection->getConnection()
        ->select()
        ->from($connection->getTableName($feedTable), ['entity_id'])
        ->limitPage($offset, $batchSize);
    return $connection->getConnection()->fetchCol($select);
}

/**
 * @param array $entityIds
 * @return array
 */
function buildEntitiesAsArray(array $entityIds): array
{
    $entitiesArray = [];
    foreach ($entityIds as $id) {
        $entitiesArray[] = [
            'entity_id' => (int)$id,
        ];
    }

    return $entitiesArray;
}

/** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
$indexerRegistry = $om->get(\Magento\Framework\Indexer\IndexerRegistry::class);
/** @var \Magento\DataExporter\Model\FeedPool $feedPool */
$feedPool = $om->get(\Magento\DataExporter\Model\FeedPool::class);
/** @var \Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder $messageBuilder */
$messageBuilder = $om->get(\Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder::class);
/** @var \Magento\Store\Model\StoreManager $storeManager */
$storeManager = $om->get(\Magento\Store\Model\StoreManager::class);
/** @var ProductsConsumer $productConsumer */
$productConsumer = $om->get(ProductsConsumer::class);
$categoriesConsumer = $om->get(CategoriesConsumer::class);

/**
 * @param string $entity
 * @param string $feedName
 * @param string $entityTable
 * @param array $consumerEventTypes
 * @param $consumer
 */
function processEntity(
    string $entity,
    string $feedName,
    string $entityTable,
    array $consumerEventTypes,
    string $identifier,
    $consumer
): void {
    global $storeManager, $feedPool, $messageBuilder, $indexerRegistry;
    $feedIndexer = $indexerRegistry->get($feedName);
    $feedIndexer->reindexAll();

    $feed = $feedPool->getFeed($entity);
    $page = 1;
    $batchSize = 1000;
    $stores = $storeManager->getStores();
    $storeCodes = array_map(function(\Magento\Store\Api\Data\StoreInterface $store) {
        return $store->getCode();
    }, $stores);
    while ($ids = getBatchIds($entityTable, $batchSize, $page)) {
        $page++;
        $deletedProducts = $feed->getDeletedByIds($ids, $storeCodes);
        $deletedIds = array_map(function (array $feedItem) use ($identifier) {
            return $feedItem[$identifier];
        }, $deletedProducts);
        $newIds = array_diff($ids, $deletedIds);

        //Process new IDs
        if ($newIds) {
            foreach ($storeCodes as $storeCode) {
                $message = $messageBuilder->build(
                    $consumerEventTypes['update'],
                    buildEntitiesAsArray($newIds),
                    $storeCode
                );
                $consumer->processMessage($message);
            }
        }
        //Process deleted IDs
        if ($deletedIds) {
            foreach ($storeCodes as $storeCode) {
                $message = $messageBuilder->build(
                    $consumerEventTypes['delete'],
                    buildEntitiesAsArray($deletedIds),
                    $storeCode
                );
                $consumer->processMessage($message);
            }
        }
    }
}

//Process products sync
processEntity(
    'products',
    'catalog_data_exporter_products',
    'catalog_product_entity',
    [
        'update' => ProductsConsumer::PRODUCTS_UPDATED_EVENT_TYPE,
        'delete' => ProductsConsumer::PRODUCTS_DELETED_EVENT_TYPE
    ],
    'productId',
    $productConsumer
);
//Process categories sync
processEntity(
    'categories',
    'catalog_data_exporter_categories',
    'catalog_category_entity',
    [
        'update' => CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE,
        'delete' => CategoriesConsumer::CATEGORIES_DELETED_EVENT_TYPE
    ],
    'categoryId',
    $categoriesConsumer
);
