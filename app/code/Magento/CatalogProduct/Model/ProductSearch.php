<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model;

use Magento\CatalogProductApi\Api\Data\ProductSearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\CatalogProductApi\Api\Data\ProductResultContainerInterfaceFactory;
use Magento\CatalogProductApi\Api\ProductSearchInterface;
use Magento\CatalogProductApi\Api\Data\ProductResultContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class ProductSearch implements ProductSearchInterface
{
    /**
     * @var ProductResultContainerInterfaceFactory
     */
    private $productResultContainerFactory;

    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductResultContainerInterfaceFactory $productResultContainerFactory
     * @param DataProviderInterface $dataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductResultContainerInterfaceFactory $productResultContainerFactory,
        DataProviderInterface $dataProvider,
        LoggerInterface $logger
    ) {
        $this->productResultContainerFactory = $productResultContainerFactory;
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
     * @param ProductSearchCriteriaInterface $criteria
     * @return ProductResultContainerInterface
     */
    private function processRequest($criteria): ProductResultContainerInterface
    {
        if (!isset($criteria->getScopes()['store'])) {
            return $this->processErrors([_('Store id is not present in Search Criteria. Please add missing info.')]);
        }
        $productIds = (array)$criteria->getFilters()['ids'];
        if (!$productIds) {
            throw new \InvalidArgumentException('Currently Catalog Storefront service supports only product ids');
        }

        $productItems = $this->dataProvider->fetch(
            $productIds,
            $criteria->getAttributes(),
            $criteria->getScopes()
        );

        return $this->productResultContainerFactory->create(
            [
                'errors' => [],
                'metaInfo' => [],
                'items' => $productItems,
                'aggregations' => [],
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
     * @param array $metaInfo
     * @return ProductResultContainerInterface
     */
    private function processErrors(array $errors, array $metaInfo = []): ProductResultContainerInterface
    {
        return $this->productResultContainerFactory->create(
            [
                'errors' => $errors,
                'metaInfo' => $metaInfo,
                'items' => [],
                'aggregations' => []
            ]
        );
    }
}
