<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogMessageBroker\Model\FetchCategoriesInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryMapper;
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
     * @param LoggerInterface $logger
     * @param FetchCategoriesInterface $fetchCategories
     * @param CatalogServerInterface $catalogServer
     * @param ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory
     * @param CategoryMapper $categoryMapper
     */
    public function __construct(
        LoggerInterface $logger,
        FetchCategoriesInterface $fetchCategories,
        CatalogServerInterface $catalogServer,
        ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory,
        CategoryMapper $categoryMapper
    ) {
        $this->logger = $logger;
        $this->fetchCategories = $fetchCategories;
        $this->catalogServer = $catalogServer;
        $this->importCategoriesRequestInterfaceFactory = $importCategoriesRequestInterfaceFactory;
        $this->categoryMapper = $categoryMapper;
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
            $dataPerStore = [];

            foreach ($this->fetchCategories->execute($ids) as $category) {
                $dataPerStore[$category['store_view_code']][] = $category;
            }

            foreach ($dataPerStore as $storeCode => $categories) {
                $this->unsetNullRecursively($categories);
                $this->importCategories($storeCode, $categories);
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
    private function unsetNullRecursively(&$haystack)
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
     * @param string $storeCode
     * @param array $categories
     *
     * @throws \Throwable
     */
    private function importCategories(string $storeCode, array $categories): void
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
}
