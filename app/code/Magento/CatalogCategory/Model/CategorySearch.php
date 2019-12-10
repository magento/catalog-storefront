<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogCategory\DataProvider\DataProviderInterface;
use Magento\CatalogCategory\Model\CategorySearch\CategoryFilter;
use Magento\CatalogCategoryApi\Api\Data\CategoryResultContainerInterfaceFactory;
use Magento\CatalogCategoryApi\Api\CategorySearchInterface;
use Magento\CatalogCategoryApi\Api\Data\CategoryResultContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class CategorySearch implements CategorySearchInterface
{
    /**
     * @var CategoryResultContainerInterfaceFactory
     */
    private $categoryResultContainerFactory;

    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CategoryFilter
     */
    private $categoryFilter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CategoryResultContainerInterfaceFactory $categoryResultContainerFactory
     * @param DataProviderInterface $dataProvider
     * @param LoggerInterface $logger
     * @param CategoryFilter $categoryFilter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CategoryResultContainerInterfaceFactory $categoryResultContainerFactory,
        DataProviderInterface $dataProvider,
        LoggerInterface $logger,
        CategoryFilter $categoryFilter,
        CollectionFactory $collectionFactory

    ) {
        $this->categoryResultContainerFactory = $categoryResultContainerFactory;
        $this->dataProvider = $dataProvider;
        $this->logger = $logger;
        $this->categoryFilter = $categoryFilter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function search(array $requests): array
    {
        $output = [];
        $responsePosition = -1;

        foreach ($requests as $criteria) {
            $responsePosition++;
            try {
                $output[$responsePosition] = $this->processRequest($criteria);
            } catch (\Throwable $exception) {
                $output[$responsePosition] = $this->processException($exception);
            }
        }

        return $output;
    }

    /**
     * Process request
     *
     * @param \Magento\CatalogCategoryApi\Api\Data\CategorySearchCriteriaInterface $criteria
     * @return CategoryResultContainerInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws LocalizedException
     */
    private function processRequest($criteria): CategoryResultContainerInterface
    {
        $errors = [];
        $categoryCollection = $this->collectionFactory->create();
        try {
            $this->categoryFilter->applyFilters($criteria->getFilters(), $categoryCollection, $criteria->getScopes());
        } catch (InputException $e) {
            // handle case \Magento\GraphQl\Catalog\CategoryListTest::testEmptyFiltersReturnRootCategory
            return $this->categoryResultContainerFactory->create(
                [
                    'errors' => $errors,
                    'categories' => []
                ]
            );
        }
        $rootCategoryIds = [];
        foreach ($categoryCollection as $category) {
            $rootCategoryIds[] = (int)$category->getId();
        }
        $categories = $this->dataProvider->fetch(
            $rootCategoryIds,
            $criteria->getAttributes(),
            $criteria->getScopes()
        );

        return $this->categoryResultContainerFactory->create(
            [
                'errors' => $errors,
                'categories' => $categories
            ]
        );
    }

    /**
     * Process exception
     *
     * @param \Throwable $exception
     * @return CategoryResultContainerInterface
     */
    private function processException(\Throwable $exception): CategoryResultContainerInterface
    {
        if ($exception instanceof LocalizedException) {
            $error = $exception->getMessage();
        } else {
            $this->logger->critical($exception);
            $error = __('An error occurred during categories search. Please, check the logs');
        }

        return $this->processErrors([$error]);
    }

    /**
     * Process errors
     *
     * @param array $errors
     * @return CategoryResultContainerInterface
     */
    private function processErrors(array $errors): CategoryResultContainerInterface
    {
        return $this->categoryResultContainerFactory->create(
            [
                'errors' => $errors,
                'categories' => []//?????
            ]
        );
    }
}
