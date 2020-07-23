<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogDataExporter\Model\Indexer\CategoryFeedIndexer;
use Magento\CatalogMessageBroker\Model\FetchCategoriesInterface;
use Magento\CatalogStorefront\Model\Storage\Client\CommandInterface;
use Magento\CatalogStorefront\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogStorefront\Model\MessageBus\Consumer as OldConsumer;
use Magento\CatalogStorefront\Model\MessageBus\CatalogItemMessageBuilder;
use Psr\Log\LoggerInterface;

/**
 * Process categories update messages and update storefront app
 */
class CategoriesConsumer extends OldConsumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FetchCategoriesInterface
     */
    private $fetchCategories;

    /**
     * CategoriesConsumer constructor.
     * @param CommandInterface $storageWriteSource
     * @param DataDefinitionInterface $storageSchemaManager
     * @param State $storageState
     * @param CatalogItemMessageBuilder $catalogItemMessageBuilder
     * @param LoggerInterface $logger
     * @param FetchCategoriesInterface $fetchCategories
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CommandInterface $storageWriteSource,
        DataDefinitionInterface $storageSchemaManager,
        State $storageState,
        CatalogItemMessageBuilder $catalogItemMessageBuilder,
        LoggerInterface $logger,
        FetchCategoriesInterface $fetchCategories,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $storageWriteSource,
            $storageSchemaManager,
            $storageState,
            $catalogItemMessageBuilder,
            $logger
        );
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->fetchCategories = $fetchCategories;
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
                $storesToIds[$store->getCode()] = $store->getId();
            }
        } catch (\Throwable $e) {
            $storesToIds['default'] = 1;
        }

        return $storesToIds;
    }

    /**
     * @param array $mappedStores
     * @param string $storeCode
     * @return int|mixed
     */
    private function resolveStoreId(array $mappedStores, string $storeCode)
    {
        //workaround for tests
        return $mappedStores[$storeCode] ?? 1;
    }

    /**
     * Process message
     *
     * @param string $ids
     */
    public function processMessage(string $ids)
    {
        try {
            $ids = json_decode($ids, true);
            $dataPerType = [];
            $categories = $this->fetchCategories->execute($ids);
            $mappedStores = $this->getMappedStores();

            foreach ($categories as $category) {
                $storeId = $this->resolveStoreId($mappedStores, $category['store_view_code']);
                $dataPerType['category'][$storeId][self::SAVE][] = $category;
            }

            $this->saveToStorage($dataPerType);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
