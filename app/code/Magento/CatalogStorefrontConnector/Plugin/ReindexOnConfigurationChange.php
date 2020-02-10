<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\CatalogStorefrontConnector\Model\UpdatedEntitiesMessageBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Throwable;

/**
 * Plugin for collect category data during saving process
 */
class ReindexOnConfigurationChange
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;
    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * Queue topic name
     */
    private const QUEUE_TOPIC = 'storefront.catalog.category.update';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var UpdatedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param PublisherInterface $queuePublisher
     * @param UpdatedEntitiesMessageBuilder $messageBuilder
     * @param IndexerRegistry $indexerRegistry
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        UpdatedEntitiesMessageBuilder $messageBuilder,
        IndexerRegistry $indexerRegistry,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        ReinitableConfigInterface $reinitableConfig
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
        $this->logger = $logger;
        $this->indexerRegistry = $indexerRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * Handle product save when indexer mode is set to "schedule"
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function afterSaveConfig(
        $path,
        $value,
        $scope,
        $scopeId
    ): void {
        if (Configuration::XML_PATH_SHOW_OUT_OF_STOCK !== $scope || $this->isIndexerRunOnSchedule()) {
            return ;
        }
        $this->reinitableConfig->reinit();
        $categoryCollection = $this->collectionFactory->create();
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $categoryId = $category->getId();
            foreach ($category->getStoreIds() as $storeId) {
                $storeId = (int)$storeId;
                if ($storeId === Store::DEFAULT_STORE_ID) {
                    continue;
                }
                $message = $this->messageBuilder->build($storeId, [$categoryId]);
                try {
                    $this->logger->debug(sprintf('Collect category id: "%s" in store %s', $categoryId, $storeId));
                    $this->queuePublisher->publish(self::QUEUE_TOPIC, $message);
                } catch (Throwable $e) {
                    $this->logger->critical(
                        sprintf('Error on collect category id "%s" in store %s', $categoryId, $storeId),
                        ['exception' => $e]
                    );
                }
            }
        }
    }

    /**
     * Is indexer run in "on schedule" mode
     *
     * @return bool
     */
    private function isIndexerRunOnSchedule(): bool
    {
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        return $indexer->isScheduled();
    }
}
