<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogMessageBroker\Model\FetchCategoriesInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryMapper;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesRequestInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesRequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterfaceFactory;

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
     */
    public function __construct(
        LoggerInterface $logger,
        FetchCategoriesInterface $fetchCategories,
        StoreManagerInterface $storeManager,
        CatalogServerInterface $catalogServer,
        ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory,
        CategoryMapper $categoryMapper,
        DeleteCategoriesRequestInterfaceFactory  $deleteCategoriesRequestInterfaceFactory
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
     * @param string $ids
     * @return void
     */
    public function processMessage(string $ids): void
    {
        try {
            $ids = json_decode($ids, true);
            $categoryPerStore = [];
            $storesToIds = $this->getMappedStores();
            $categories = $this->fetchCategories->getByIds($ids);

            if (!empty($categories)) {
                foreach ($categories as $override) {
                    $storeId = $this->resolveStoreId($storesToIds, $override['store_view_code']);
                    $categoryPerStore[$storeId][] = $override;
                }
                foreach ($categoryPerStore as $storeId => $categories) {
                    $this->unsetNullRecursively($categories);
                    $this->importCategories($storeId, $categories);
                }
            }

            // @todo temporary solution. Deleted categories must be processed from different message in queue
            // message must be published into \Magento\CatalogDataExporter\Model\Indexer\CategoryFeedIndexer::process
            $deletedCategories = $this->fetchCategories->getDeleted($ids);
            if (!empty($deletedCategories)) {
                $this->deleteCategories($deletedCategories, $storesToIds);
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Recursively unset array elements equal to NULL.
     *
     * @TODO: Eliminate duplicate
     * \Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher::unsetNullRecursively
     *
     * @param array $haystack
     * @return void
     */
    private function unsetNullRecursively(&$haystack): void
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $this->unsetNullRecursively($haystack[$key]);
            }
            if ($haystack[$key] === null) {
                unset($haystack[$key]);
            }
        }
    }

    /**
     * Import categories
     *
     * @param int $storeId
     * @param array $categories
     * @throws \Throwable
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
        //todo: the expected storeId format should be int
        $importCategoriesRequest->setStore((string)$storeId);
        $importResult = $this->catalogServer->importCategories($importCategoriesRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Categories import is failed: "%s"', $importResult->getMessage()));
        }
    }

    /**
     * Deleted products from storage
     *
     * @param array $deletedCategories
     * @param array $storesToIds
     */
    private function deleteCategories(array $deletedCategories, array $storesToIds)
    {
        $categoriesPerStore = [];
        foreach ($deletedCategories as $category) {
            $storeId = $storesToIds[$category['store_view_code']];
            $categoriesPerStore[$storeId][$category['category_id']] = $category;
        }

        foreach ($categoriesPerStore as $storeId => $categories) {
            try {
                /** @var DeleteCategoriesRequestInterface $deleteCategoryRequest */
                $deleteCategoryRequest = $this->deleteCategoriesRequestInterfaceFactory->create();
                $deleteCategoryRequest->setCategoryIds(\array_keys($categories));
                $deleteCategoryRequest->setStore((string)$storeId);

                $this->catalogServer->deleteCategories($deleteCategoryRequest);
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }
    }
}
