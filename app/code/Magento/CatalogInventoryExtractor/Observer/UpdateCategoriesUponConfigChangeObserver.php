<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryExtractor\Observer;

use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogStorefrontConnector\Plugin\CategoryUpdatesPublisher;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Observer for update categories data after "Show Out Of Stock Products" config changed
 */
class UpdateCategoriesUponConfigChangeObserver implements ObserverInterface
{

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
     * @param CategoryUpdatesPublisher $categoryPublisher
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryUpdatesPublisher $categoryPublisher,
        CatalogEntityIdsProvider $catalogEntityIdsProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryPublisher = $categoryPublisher;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * Update categories data on stock configuration change
     *
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $changedPaths = (array) $observer->getEvent()->getChangedPaths();

        if (\in_array(Configuration::XML_PATH_SHOW_OUT_OF_STOCK, $changedPaths, true)) {
            foreach ($this->storeManager->getStores() as $store) {
                foreach ($this->catalogEntityIdsProvider->getCategoryIds($store->getId()) as $categoryIds) {
                    $this->categoryPublisher->publish($categoryIds, $store->getId());
                }
            }
        }
    }
}
