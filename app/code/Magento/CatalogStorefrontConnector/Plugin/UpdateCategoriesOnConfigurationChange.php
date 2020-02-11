<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Plugin;

use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Plugin for collect category data during saving process
 */
class UpdateCategoriesOnConfigurationChange
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
     * @var CategoryUpdatesPublisher
     */
    private $categoryPublisher;

    /**
     * @var CatalogEntityIdsProvider
     */
    private $catalogEntityIdsProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * UpdateCategoriesOnConfigurationChange constructor.
     * @param IndexerRegistry $indexerRegistry
     * @param ReinitableConfigInterface $reinitableConfig
     * @param CategoryUpdatesPublisher $categoryPublisher
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ReinitableConfigInterface $reinitableConfig,
        CategoryUpdatesPublisher $categoryPublisher,
        CatalogEntityIdsProvider $catalogEntityIdsProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->reinitableConfig = $reinitableConfig;
        $this->categoryPublisher = $categoryPublisher;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * Update categories data on stock configuration change
     *
     * @param Config $subject
     * @param Config $result
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return Config $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function afterSaveConfig(
        Config $subject,
        Config $result,
        string $path,
        string $value,
        string $scope,
        int $scopeId
    ): Config {
        if (Configuration::XML_PATH_SHOW_OUT_OF_STOCK !== $scope || $this->isIndexerRunOnSchedule()) {
            return $result;
        }
        $this->reinitableConfig->reinit();

        foreach ($this->storeManager->getStores() as $store) {
            $storeId = $store->getId();
            foreach ($this->catalogEntityIdsProvider->getCategoryIds($storeId) as $categoryIds) {
                $this->categoryPublisher->publish($categoryIds, $storeId);
            }
        }

        return $result;
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
