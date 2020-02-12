<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryExtractor\Plugin;

use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogStorefrontConnector\Plugin\CategoryUpdatesPublisher;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Plugin for update categories data after "Show Out Of Stock Products" config changed
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
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ): Config {
        if (Configuration::XML_PATH_SHOW_OUT_OF_STOCK !== $path || $this->isIndexerRunOnSchedule()) {
            return $result;
        }
        $this->reinitableConfig->reinit();

        foreach ($this->storeManager->getStores() as $store) {
            $storeId = (int)$store->getId();
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
