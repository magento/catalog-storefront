<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogProduct\DataProvider\ProductDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogProduct\DataProvider\LayeredNavigation\LayerBuilder;
use Magento\CatalogProductApi\Api\Data\ProductResultContainerInterfaceFactory;
use Magento\CatalogProductApi\Api\ProductSearchInterface;
use Magento\Search\Api\SearchInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\CatalogProduct\DataProvider\SearchCriteriaBuilder;
use Magento\CatalogProductApi\Api\Data\ProductResultContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class ProductSearch implements ProductSearchInterface
{
    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var LayerBuilder
     */
    private $layerBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

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
     * @param SearchInterface $search
     * @param LayerBuilder $layerBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductResultContainerInterfaceFactory $productResultContainerFactory
     * @param ProductDataProvider $dataProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        SearchInterface $search,
        LayerBuilder $layerBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductResultContainerInterfaceFactory $productResultContainerFactory,
        ProductDataProvider $dataProvider,
        LoggerInterface $logger
    ) {
        $this->search = $search;
        $this->layerBuilder = $layerBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
     * @param \Magento\CatalogProductApi\Api\Data\ProductSearchCriteriaInterface $criteria
     * @return ProductResultContainerInterface
     */
    private function processRequest($criteria): ProductResultContainerInterface
    {
        if (!isset($criteria->getScopes()['store'])) {
            return $this->processErrors([_('Store id is not present in Search Criteria. Please add missing info.')]);
        }
        $storeId = (int)$criteria->getScopes()['store'];

        $searchCriteria = $this->searchCriteriaBuilder->build($criteria);
        $showLayeredNavigation = !empty($criteria->getAggregations()) || $criteria->getAggregations() === null;
        [$totalCount, $productIds, $aggregations] = $this->searchProducts(
            $searchCriteria,
            $showLayeredNavigation
        );

        $currentPage = $searchCriteria->getCurrentPage();
        $maxPages = $this->getTotalPages($searchCriteria, $totalCount);
        $metaInfo = [
            'page_size' => $searchCriteria->getPageSize(),
            'current_page' => $searchCriteria->getCurrentPage(),
            'total_pages' => $this->getTotalPages($searchCriteria, $totalCount),
            'total_count' => $totalCount,
            // for backward compatibility: support "filters" field
            'layer_type' => $criteria->getSearchTerm()
                ? LayerResolver::CATALOG_LAYER_SEARCH
                : LayerResolver::CATALOG_LAYER_CATEGORY,
        ];

        if ($currentPage > $maxPages && $totalCount > 0) {
            return $this->processErrors(
                [
                    __(
                        'currentPage value %1 specified is greater than the %2 page(s) available.',
                        $currentPage,
                        $maxPages
                    )
                ],
                $metaInfo
            );
        }

        $productItems = $this->dataProvider->fetch(
            $productIds,
            $criteria->getAttributes(),
            $criteria->getScopes()
        );

        return $this->productResultContainerFactory->create(
            [
                'errors' => [],
                'metaInfo' => $metaInfo,
                'items' => $productItems,
                'aggregations' => $showLayeredNavigation ? $this->layerBuilder->build($aggregations, $storeId) : []
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

    /**
     * Search product using Search API
     *
     * @param SearchCriteriaInterface $origSearchCriteria
     * @param bool $showLayeredNavigation
     * @return array
     */
    private function searchProducts(SearchCriteriaInterface $origSearchCriteria, bool $showLayeredNavigation): array
    {
        $searchCriteria = clone $origSearchCriteria;
        $searchCriteria->setCurrentPage($searchCriteria->getCurrentPage() - 1);
        $itemsResults = $this->search->search($searchCriteria);

        $productIds = [];
        foreach ($itemsResults->getItems() as $item) {
            $productIds[] = $item->getId();
        }

        return [
            $itemsResults->getTotalCount(),
            $productIds,
            $showLayeredNavigation ? $itemsResults->getAggregations() : null
        ];
    }

    /**
     * Get total pages count for request
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param int $totalCount
     * @return int
     */
    private function getTotalPages($searchCriteria, $totalCount): int
    {
        $maxPages = $searchCriteria->getPageSize() > 0
            ? \ceil($totalCount / $searchCriteria->getPageSize())
            : 0;

        return (int) $maxPages;
    }
}
