<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\CatalogStorefrontApi\Api\Data\ProductCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogStorefront\DataProvider\ProductDataProvider;
use Magento\CatalogStorefrontApi\Api\Data\ProductResultContainerInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\ProductInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductResultContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 * @deprecated
 */
class ProductSearch
{
    /**
     * @var ProductResultContainerInterfaceFactory
     */
    private $productResultContainerFactory;

    /**
     * @var ProductDataProvider
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductResultContainerInterfaceFactory $productResultContainerFactory
     * @param ProductDataProvider $dataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductResultContainerInterfaceFactory $productResultContainerFactory,
        ProductDataProvider $dataProvider,
        LoggerInterface $logger
    ) {
        $this->productResultContainerFactory = $productResultContainerFactory;
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
     * @param ProductCriteriaInterface $criteria
     * @return ProductResultContainerInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function processRequest($criteria): ProductResultContainerInterface
    {
        if (!isset($criteria->getScopes()['store'])) {
            return $this->processErrors([_('Store id is not present in Search Criteria. Please add missing info.')]);
        }

        $productItems = [];
        if (!empty($criteria->getIds())) {
            $productItems = $this->dataProvider->fetch(
                $criteria->getIds(),
                $criteria->getAttributes(),
                $criteria->getScopes()
            );
        }

        return $this->productResultContainerFactory->create(
            [
                'errors' => [],
                'items' => $productItems,
            ]
        );
    }

    /**
     * Process exception
     *
     * @param \Throwable $exception
     * @return ProductResultContainerInterface
     */
    private function processException(\Throwable $exception): ProductResultContainerInterface
    {
        if ($exception instanceof LocalizedException) {
            $error = $exception->getMessage();
        } else {
            $this->logger->critical($exception);
            $error = __('An error occurred during products search. Please, check the logs');
        }

        return $this->processErrors([$error]);
    }

    /**
     * Process errors
     *
     * @param array $errors
     * @return ProductResultContainerInterface
     */
    private function processErrors(array $errors): ProductResultContainerInterface
    {
        return $this->productResultContainerFactory->create(
            [
                'errors' => $errors,
                'items' => [],
            ]
        );
    }
}
