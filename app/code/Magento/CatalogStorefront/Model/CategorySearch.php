<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\Framework\Exception\LocalizedException;
// TODO: replace with CategoryProvider
use Magento\CategoryExtractor\DataProvider\DataProviderInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryResultContainerInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\CategorySearchInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryResultContainerInterface;
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
     * @param CategoryResultContainerInterfaceFactory $categoryResultContainerFactory
     * @param DataProviderInterface $dataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategoryResultContainerInterfaceFactory $categoryResultContainerFactory,
        DataProviderInterface $dataProvider,
        LoggerInterface $logger
    ) {
        $this->categoryResultContainerFactory = $categoryResultContainerFactory;
        $this->dataProvider = $dataProvider;
        $this->logger = $logger;
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
     * @param \Magento\CatalogStorefrontApi\Api\Data\CategorySearchCriteriaInterface $criteria
     * @return CategoryResultContainerInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws LocalizedException
     */
    private function processRequest($criteria): CategoryResultContainerInterface
    {
        if (!isset($criteria->getFilters()['ids'])) {
            throw new \InvalidArgumentException('Currently Catalog Storefront service supports only category ids');
        }
        $rootCategoryIds = (array)$criteria->getFilters()['ids'];

        $categories = $this->dataProvider->fetch(
            $rootCategoryIds,
            \array_merge($criteria->getAttributes(), ['is_active']),
            $criteria->getScopes()
        );

        //TODO: Move to CategoryDataProvider
        foreach ($categories as $n => $category) {
            if (empty($category['is_active'])) {
                unset($categories[$n]);
            }
        }
        return $this->categoryResultContainerFactory->create(
            [
                'errors' => [],
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
                'categories' => []
            ]
        );
    }
}
