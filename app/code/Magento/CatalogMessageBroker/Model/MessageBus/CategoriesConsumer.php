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
     * Process message
     *
     * @param string $ids
     */
    public function processMessage(string $ids)
    {
        try {
            //For test purposes
            $ids = json_decode($ids, true);
            $dataPerType = [];
            $categories = $this->fetchCategories->execute($ids);

            // @todo eliminate store manager
            $stores = $this->storeManager->getStores(true);
            $storesToIds = [];
            foreach ($stores as $store) {
                $storesToIds[$store->getCode()] = $store->getId();
            }

            foreach ($categories as $category) {
                //workaround for tests
                $storeId = $storesToIds[$category['store_view_code']] ?? 1;
                $dataPerType['category'][$storeId][self::SAVE][] = $category;
            }

            $this->saveToStorage($dataPerType);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
