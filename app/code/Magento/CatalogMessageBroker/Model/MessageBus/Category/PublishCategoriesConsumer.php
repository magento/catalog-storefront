<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Category;

use Magento\CatalogMessageBroker\Model\FetchCategoriesInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventData;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryMapper;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterfaceFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoryRequestAttributesMapper;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Psr\Log\LoggerInterface;

/**
 * Publish categories into storage
 */
class PublishCategoriesConsumer implements ConsumerEventInterface
{
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
     * @var ImportCategoryRequestAttributesMapper
     */
    private $importCategoryRequestAttributesMapper;

    /**
     * @param LoggerInterface $logger
     * @param FetchCategoriesInterface $fetchCategories
     * @param CatalogServerInterface $catalogServer
     * @param ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory
     * @param CategoryMapper $categoryMapper
     * @param ImportCategoryRequestAttributesMapper $importCategoryRequestAttributesMapper
     */
    public function __construct(
        LoggerInterface $logger,
        FetchCategoriesInterface $fetchCategories,
        CatalogServerInterface $catalogServer,
        ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory,
        CategoryMapper $categoryMapper,
        ImportCategoryRequestAttributesMapper $importCategoryRequestAttributesMapper
    ) {
        $this->logger = $logger;
        $this->fetchCategories = $fetchCategories;
        $this->catalogServer = $catalogServer;
        $this->importCategoriesRequestInterfaceFactory = $importCategoriesRequestInterfaceFactory;
        $this->categoryMapper = $categoryMapper;
        $this->importCategoryRequestAttributesMapper = $importCategoryRequestAttributesMapper;
    }

    /**
     * @inheritdoc
     */
    public function execute(EventData $eventData): void
    {
        $categoriesData = $this->fetchCategories->execute($eventData);
        $eventEntities = $eventData->getEntities();
        $importCategories = [];
        $updateCategories = [];

        foreach ($categoriesData as $categoryData) {
            $eventCategory = $eventEntities[$categoryData['category_id']];

            if (!empty($eventCategory->getAttributes())) {
                $updateCategories[$categoryData['category_id']] = \array_filter(
                    $categoryData,
                    function ($code) use ($eventCategory) {
                        return \in_array($code, \array_map(function ($attributeCode) {
                            return SimpleDataObjectConverter::camelCaseToSnakeCase($attributeCode);
                        }, $eventCategory->getAttributes()));
                    },
                    ARRAY_FILTER_USE_KEY
                );
            } else {
                $importCategories[$categoryData['category_id']] = $categoryData;
            }
        }

        if (!empty($importCategories)) {
            $this->importCategories($importCategories, $eventData->getScope());
        }

        if (!empty($updateCategories)) {
            $this->updateCategories($updateCategories, $eventData->getScope());
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

        $result = $this->import($categories, $storeCode);

        if ($result->getStatus() === false) {
            $this->logger->error(sprintf('Categories import is failed: "%s"', $result->getMessage()));
        }
    }

    /**
     * Update categories
     *
     * @param array $categories
     * @param string $storeCode
     *
     * @return void
     */
    private function updateCategories(array $categories, string $storeCode): void
    {
        $attributes = [];

        foreach ($categories as &$category) {
            // be sure, that data passed to Import API in the expected format
            $category['id'] = $category['category_id'];

            $attributes[] = $this->importCategoryRequestAttributesMapper->setData([
                'entity_id' => $category['category_id'],
                'attribute_codes' => \array_keys($category),
            ])->build();

            $category = $this->categoryMapper->setData($category)->build();
        }

        $result = $this->import($categories, $storeCode, $attributes);

        if ($result->getStatus() === false) {
            $this->logger->error(sprintf('Categories update is failed: "%s"', $result->getMessage()));
        }
    }

    /**
     * Perform category import / update
     *
     * @param array $categories
     * @param string $storeCode
     * @param array $attributes
     *
     * @return ImportCategoriesResponseInterface
     */
    private function import(
        array $categories,
        string $storeCode,
        array $attributes = []
    ): ImportCategoriesResponseInterface {
        $importCategoriesRequest = $this->importCategoriesRequestInterfaceFactory->create();
        $importCategoriesRequest->setCategories($categories);
        $importCategoriesRequest->setStore($storeCode);
        $importCategoriesRequest->setAttributes($attributes);

        return $this->catalogServer->importCategories($importCategoriesRequest);
    }
}
