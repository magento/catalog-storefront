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
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesRequestInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterfaceFactory;
use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterfaceV2;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Process categories update messages and update storefront app
 */
class CategoriesConsumer
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

    /**
     * Process message
     *
     * @param UpdatedEntitiesDataInterfaceV2 $message
     * @return void
     */
    public function processMessage(UpdatedEntitiesDataInterfaceV2 $message): void
    {
        try {
            $storesToIds = $this->getMappedStores();

            if ($message->getMeta()['type'] === \Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataV2::CATEGORIES_UPDATED_EVENT_TYPE) {
                $categories = $this->fetchCategories->getByIds($message->getData()['ids']);
                if (!empty($categories)) {
                    $categoryPerStore = [];
                    foreach ($categories as $override) {
                        $dataStoreId = $this->resolveStoreId($storesToIds, $override['store_view_code']);
                        $categoryPerStore[$dataStoreId][] = $override;
                    }
                    foreach ($categoryPerStore as $storeId => $categories) {
                        $this->importCategories($storeId, $categories);
                    }
                }
            } elseif ($message->getMeta()['type'] === \Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataV2::CATEGORIES_DELETED_EVENT_TYPE) {
                $this->deleteCategories($message->getData()['ids'], $message->getData()['storeId']);
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Import categories to storage
     *
     * @param int $storeId
     * @param array $categories
     * @return void
     */
    private function importCategories(int $storeId, array $categories): void
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
     * @param int $storeId
     * @return void
     */
    private function deleteCategories(array $categoryIds, int $storeId): void
    {
        /** @var DeleteCategoriesRequestInterface $deleteCategoryRequest */
        $deleteCategoryRequest = $this->deleteCategoriesRequestInterfaceFactory->create();
        $deleteCategoryRequest->setCategoryIds($categoryIds);
        $deleteCategoryRequest->setStore((string)$storeId);
        try {
            $importResult = $this->catalogServer->deleteCategories($deleteCategoryRequest);
            if ($importResult->getStatus() === false) {
                $this->logger->error(sprintf('Categories deletion has failed: "%s"', $importResult->getMessage()));
            }
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while deleting categories: "%s"', $e));
        }
    }
}
