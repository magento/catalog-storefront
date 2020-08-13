<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface;
use Magento\CatalogDataExporter\Model\Feed\Products as ProductsFeed;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\ProductsConsumer;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Consumer processes messages with store front products data
 */
class ProductsQueueConsumer
{
    const BATCH_SIZE = 100;

    /**
     * @var CatalogEntityIdsProvider
     */
    private $catalogEntityIdsProvider;

    /**
     * @var ProductsConsumer
     */
    private $productsConsumer;

    /**
     * @var ProductFeedIndexer
     */
    private $productFeedIndexer;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;
    /**
     * @var ProductsFeed
     */
    private $productsFeed;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductsConsumer $productsConsumer
     * @param ProductFeedIndexer $productFeedIndexer
     * @param StoreManagerInterface $storeManager
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param LoggerInterface $logger
     * @param ProductsFeed $productsFeed
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     */
    public function __construct(
        ProductsConsumer $productsConsumer,
        ProductFeedIndexer $productFeedIndexer,
        StoreManagerInterface $storeManager,
        ChangedEntitiesMessageBuilder $messageBuilder,
        LoggerInterface $logger,
        ProductsFeed $productsFeed,
        CatalogEntityIdsProvider $catalogEntityIdsProvider
    ) {
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->productsConsumer = $productsConsumer;
        $this->productFeedIndexer = $productFeedIndexer;
        $this->storeManager = $storeManager;
        $this->messageBuilder = $messageBuilder;
        $this->productsFeed = $productsFeed;
        $this->logger = $logger;
    }

    /**
     * Process collected product IDs for update
     * TODO: Eliminate the redundant calls. The incoming message is storeId specific, the outgoing, is not.
     *
     * Process messages from storefront.catalog.product.update topic
     *
     * @param UpdatedEntitiesDataInterface $message
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated React on events triggered by plugins to push data to SF storage
     */
    public function processMessages(UpdatedEntitiesDataInterface $message): void
    {
        //wtf? is this storeview specific?
        $incomingStoreId = $message->getStoreId();
        $ids = $message->getEntityIds();

        if (empty($ids)) {
            $this->productFeedIndexer->executeFull();
            foreach ($this->catalogEntityIdsProvider->getProductIds($incomingStoreId) as $idsChunk) {
                $ids[] = $idsChunk;
            }
        } else {
            $this->productFeedIndexer->executeList($ids);
        }

        $storesToIds = $this->getMappedStores();
        $deleted = [];
        foreach ($this->productsFeed->getDeletedByIds($ids) as $product) {
            $storeId = $this->resolveStoreId($storesToIds, $product['storeViewCode']);
            $deleted[$storeId][] = $product['productId'];
            unset($ids[$product['productId']]);
        }

        foreach ($deleted as $storeId => $entityIds) {
            foreach (array_chunk($entityIds, self::BATCH_SIZE) as $idsChunk) {
                if (!empty($idsChunk && $storeId === $incomingStoreId)) {
                    $this->passMessage(
                        ProductsConsumer::PRODUCTS_DELETED_EVENT_TYPE,
                        $idsChunk,
                        (string)$storeId
                    );
                }
            }
        }
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                $this->passMessage(
                    ProductsConsumer::PRODUCTS_UPDATED_EVENT_TYPE,
                    $idsChunk,
                );
            }
        }
    }

    /**
     * Publish deleted or updated message
     * TODO: Eliminate the redundant calls. The incoming message is storeId specific.
     *
     * @param string $eventType
     * @param int[] $ids
     * @param null $scope
     */
    private function passMessage($eventType, $ids, $scope = null)
    {
        $message = $this->messageBuilder->build(
            $ids,
            $eventType,
            $scope
        );
        try {
            $this->productsConsumer->processMessage($message);
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
