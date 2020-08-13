<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogMessageBroker\Model\FetchCategoriesInterface;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryMapper;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesRequestInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterfaceFactory;
use Magento\CatalogExport\Model\Data\ChangedEntitiesDataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Process categories update messages and update storefront app
 */
class CategoriesConsumer
{
    /**
     * Event types
     */
    const CATEGORIES_UPDATED_EVENT_TYPE = 'categories_updated';

    const CATEGORIES_DELETED_EVENT_TYPE = 'categories_deleted';

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
     * @var CatalogServerInterface
     */
    private $catalogServer;
    /**
     * @var ImportCategoriesRequestInterfaceFactory
     */
    private $importCategoriesRequestInterfaceFactory;

    /**
     * @var CategoryMapper
     */
    private $categoryMapper;

    /**
     * @var DeleteCategoriesRequestInterfaceFactory
     */
    private $deleteCategoriesRequestInterfaceFactory;

    /**
     * @param LoggerInterface $logger
     * @param FetchCategoriesInterface $fetchCategories
     * @param StoreManagerInterface $storeManager
     * @param CatalogServerInterface $catalogServer
     * @param ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory
     * @param CategoryMapper $categoryMapper
     * @param DeleteCategoriesRequestInterfaceFactory $deleteCategoriesRequestInterfaceFactory
     */
    public function __construct(
        LoggerInterface $logger,
        FetchCategoriesInterface $fetchCategories,
        StoreManagerInterface $storeManager,
        CatalogServerInterface $catalogServer,
        ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory,
        CategoryMapper $categoryMapper,
        DeleteCategoriesRequestInterfaceFactory $deleteCategoriesRequestInterfaceFactory
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->fetchCategories = $fetchCategories;
        $this->catalogServer = $catalogServer;
        $this->importCategoriesRequestInterfaceFactory = $importCategoriesRequestInterfaceFactory;
        $this->categoryMapper = $categoryMapper;
        $this->deleteCategoriesRequestInterfaceFactory = $deleteCategoriesRequestInterfaceFactory;
    }

    /**
     * Process message
     *
     * @param ChangedEntitiesDataInterface $message
     * @return void
     */
    public function processMessage(ChangedEntitiesDataInterface $message): void
    {
        try {
            $storesToIds = $this->getMappedStores();

            if ($message->getEventType() === self::CATEGORIES_UPDATED_EVENT_TYPE) {
                $categoriesData = $this->fetchCategories->getByIds($message->getEntityIds());
                if (!empty($categoriesData)) {
                    $categoriesPerStore = [];
                    foreach ($categoriesData as $categoryData) {
                        $dataStoreId = $this->resolveStoreId($storesToIds, $categoryData['store_view_code']);
                        $categoriesPerStore[$dataStoreId][] = $categoryData;
                    }
                    foreach ($categoriesPerStore as $storeId => $categories) {
                        $this->importCategories($categories, $storeId);
                    }
                }
            } elseif ($message->getEventType() === self::CATEGORIES_DELETED_EVENT_TYPE) {
                $this->deleteCategories($message->getEntityIds(), $message->getScope());
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Import categories to storage
     *
     * @param array $categories
     * @param int $storeId
     * @return void
     */
    private function importCategories(array $categories, int $storeId): void
    {
        foreach ($categories as &$category) {
            // be sure, that data passed to Import API in the expected format
            $category['id'] = $category['category_id'];
            $category = $this->categoryMapper->setData($category)->build();
        }
        $importCategoriesRequest = $this->importCategoriesRequestInterfaceFactory->create();
        $importCategoriesRequest->setCategories($categories);
        $importCategoriesRequest->setStore((string)$storeId);

        try {
            $importResult = $this->catalogServer->importCategories($importCategoriesRequest);
            if ($importResult->getStatus() === false) {
                $this->logger->error(sprintf('Categories import has failed: "%s"', $importResult->getMessage()));
            }
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while importing categories: "%s"', $e));
        }
    }

    /**
     * Delete categories from storage
     *
     * @param array $categoryIds
     * @param string $storeId
     * @return void
     */
    private function deleteCategories(array $categoryIds, string $storeId): void
    {
        $deleteCategoryRequest = $this->deleteCategoriesRequestInterfaceFactory->create();
        $deleteCategoryRequest->setCategoryIds($categoryIds);
        $deleteCategoryRequest->setStore($storeId);
        try {
            $importResult = $this->catalogServer->deleteCategories($deleteCategoryRequest);
            if ($importResult->getStatus() === false) {
                $this->logger->error(sprintf('Categories deletion has failed: "%s"', $importResult->getMessage()));
            }
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while deleting categories: "%s"', $e));
        }
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
     * Resolve store ID by store code
     *
     * @param array $mappedStores
     * @param string $storeCode
     * @return int|mixed
     */
    private function resolveStoreId(array $mappedStores, string $storeCode)
    {
        //workaround for tests
        return $mappedStores[$storeCode] ?? 1;
    }
}
