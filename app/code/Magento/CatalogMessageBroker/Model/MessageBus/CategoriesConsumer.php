<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogExport\Model\Data\ChangedEntitiesInterface;
use Magento\CatalogMessageBroker\Model\FetchCategoriesInterface;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryMapper;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesRequestInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * Process categories update messages and update storefront app
 */
class CategoriesConsumer
{
    /**
     * Event types to handle incoming messages from Export API
     * TODO: make private after https://github.com/magento/catalog-storefront/issues/242
     */
    const CATEGORIES_UPDATED_EVENT_TYPE = 'categories_updated';
    const CATEGORIES_DELETED_EVENT_TYPE = 'categories_deleted';

    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @param CatalogServerInterface $catalogServer
     * @param ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory
     * @param CategoryMapper $categoryMapper
     * @param DeleteCategoriesRequestInterfaceFactory $deleteCategoriesRequestInterfaceFactory
     */
    public function __construct(
        LoggerInterface $logger,
        FetchCategoriesInterface $fetchCategories,
        CatalogServerInterface $catalogServer,
        ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory,
        CategoryMapper $categoryMapper,
        DeleteCategoriesRequestInterfaceFactory $deleteCategoriesRequestInterfaceFactory
    ) {
        $this->logger = $logger;
        $this->fetchCategories = $fetchCategories;
        $this->catalogServer = $catalogServer;
        $this->importCategoriesRequestInterfaceFactory = $importCategoriesRequestInterfaceFactory;
        $this->categoryMapper = $categoryMapper;
        $this->deleteCategoriesRequestInterfaceFactory = $deleteCategoriesRequestInterfaceFactory;
    }

    /**
     * Process message
     *
     * @param \Magento\CatalogExport\Model\Data\ChangedEntitiesInterface $message
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processMessage(ChangedEntitiesInterface $message)
    {
        try {
            $eventType = $message->getMeta() ? $message->getMeta()->getEventType() : null;
            $scope = $message->getMeta() ? $message->getMeta()->getScope() : null;
            $entityIds = $message->getData() ? $message->getData()->getIds() : null;

            if (empty($entityIds)) {
                throw new \InvalidArgumentException('Update/delete message payload is missing category Ids');
            }

            if ($eventType === self::CATEGORIES_UPDATED_EVENT_TYPE) {
                /**
                 * TODO: Can shorten this when/if we can be sure that store_code is always passed in the $message
                 */
                $categoriesData = $this->fetchCategories->getByIds(
                    $entityIds,
                    array_filter([$scope])
                );
                if (!empty($categoriesData)) {
                    $categoriesPerStore = [];
                    foreach ($categoriesData as $categoryData) {
                        $categoriesPerStore[$categoryData['store_view_code']][] = $categoryData;
                    }
                    foreach ($categoriesPerStore as $storeCode => $categories) {
                        $this->importCategories($categories, $storeCode);
                    }
                }
            } elseif ($eventType === self::CATEGORIES_DELETED_EVENT_TYPE) {
                $this->deleteCategories($entityIds, $scope);
            } else {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'The provided event type "%s" was not recognized',
                        $eventType
                    )
                );
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Import categories
     *
     * @param array $categories
     * @param string $storeCode
     * @return void
     * @throws \Throwable
     */
    private function importCategories(array $categories, string $storeCode): void
    {
        foreach ($categories as &$category) {
            // be sure, that data passed to Import API in the expected format
            $category['id'] = $category['category_id'];
            $category = $this->categoryMapper->setData($category)->build();
        }

        $importCategoriesRequest = $this->importCategoriesRequestInterfaceFactory->create();
        $importCategoriesRequest->setCategories($categories);
        $importCategoriesRequest->setStore($storeCode);
        $importResult = $this->catalogServer->importCategories($importCategoriesRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Categories import is failed: "%s"', $importResult->getMessage()));
        }
    }

    /**
     * Delete categories from storage
     *
     * @param array $categoryIds
     * @param string $storeCode
     * @return void
     */
    private function deleteCategories(array $categoryIds, string $storeCode): void
    {
        $deleteCategoryRequest = $this->deleteCategoriesRequestInterfaceFactory->create();
        $deleteCategoryRequest->setCategoryIds($categoryIds);
        $deleteCategoryRequest->setStore($storeCode);
        $importResult = $this->catalogServer->deleteCategories($deleteCategoryRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Categories deletion has failed: "%s"', $importResult->getMessage()));
        }
    }
}
