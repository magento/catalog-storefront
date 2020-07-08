<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogMessageBroker\Model\FetchCategoriesInterface;
use Magento\CatalogStorefront\Model\Storage\Client\CommandInterface;
use Magento\CatalogStorefront\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogStorefront\Model\MessageBus\Consumer as OldConsumer;
use Magento\CatalogStorefront\Model\MessageBus\CatalogItemMessageBuilder;
use Magento\Framework\App\State as AppState;
use Psr\Log\LoggerInterface;
use Magento\CatalogMessageBroker\Model\ProductDataProcessor;

/**
 * Process categories update messages and update storefront app
 */
class CategoriesConsumer extends OldConsumer
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var ProductDataProcessor
     */
    private $productDataProcessor;

    /**
     * @var FetchCategoriesInterface
     */
    private $fetchCategories;

    /**
     * @param CommandInterface $storageWriteSource
     * @param DataDefinitionInterface $storageSchemaManager
     * @param State $storageState
     * @param CatalogItemMessageBuilder $catalogItemMessageBuilder
     * @param LoggerInterface $logger
     * @param DataProviderInterface $dataProvider
     * @param FetchCategoriesInterface $fetchCategories
     * @param StoreManagerInterface $storeManager
     * @param AppState $appState
     * @param ProductDataProcessor $productDataProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CommandInterface $storageWriteSource,
        DataDefinitionInterface $storageSchemaManager,
        State $storageState,
        CatalogItemMessageBuilder $catalogItemMessageBuilder,
        LoggerInterface $logger,
        DataProviderInterface $dataProvider,
        FetchCategoriesInterface $fetchCategories,
        StoreManagerInterface $storeManager,
        AppState $appState,
        ProductDataProcessor $productDataProcessor
    ) {
        parent::__construct(
            $storageWriteSource,
            $storageSchemaManager,
            $storageState,
            $catalogItemMessageBuilder,
            $logger
        );
        $this->logger = $logger;
        $this->dataProvider = $dataProvider;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->productDataProcessor = $productDataProcessor;
        $this->fetchCategories = $fetchCategories;
    }

    /**
     * Process message
     *
     * @param string $ids
     */
    public function processMessage(string $ids)
    {
        $ids = json_decode($ids, true);
        $dataPerType = [];
        $categories = $this->fetchCategories->execute($ids);

        foreach ($categories as $category) {
            //@TODO: resolve issue with stores
            $dataPerType['category'][0][self::SAVE][] = $category;
        }

        try {
            $this->saveToStorage($dataPerType);
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }
}
