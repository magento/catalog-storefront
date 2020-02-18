<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogStorefront\DataProvider\CategoryDataProvider;
use Magento\CatalogStorefrontApi\Api\Data\CategoryResultContainerInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\CategoryInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryResultContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class CategorySearch implements CategoryInterface
{
    /**
     * @var CategoryResultContainerInterfaceFactory
     */
    private $categoryResultContainerFactory;

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CategoryResultContainerInterfaceFactory $categoryResultContainerFactory
     * @param CategoryDataProvider $dataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategoryResultContainerInterfaceFactory $categoryResultContainerFactory,
        CategoryDataProvider $dataProvider,
        LoggerInterface $logger
    ) {
        $this->categoryResultContainerFactory = $categoryResultContainerFactory;
        $this->dataProvider = $dataProvider;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function get(array $requests): array
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
     * @param \Magento\CatalogStorefrontApi\Api\Data\CategoryCriteriaInterface $criteria
     * @return CategoryResultContainerInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function processRequest($criteria): CategoryResultContainerInterface
    {
        $categories = $this->dataProvider->fetch(
            $criteria->getIds(),
            \array_merge($criteria->getAttributes(), ['is_active']),
            $criteria->getScopes()
        );

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
