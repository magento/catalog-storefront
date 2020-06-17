<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Model;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Build search criteria for search products
 */
class SearchCriteriaBuilder
{
    /**
     * Default page size.
     *
     * Page size that is used if pageSize criteria not given.
     */
    private const DEFAULT_PAGE_SIZE = 20;

    /**
     * Default current page.
     *
     * Page number that is used if current page criteria not given.
     */
    private const DEFAULT_CURRENT_PAGE = 1;

    /**
     * Name of the request that should include aggregation in the result.
     */
    private const REQUEST_NAME_WITH_AGGREGATION = 'graphql_product_search_with_aggregation';

    /**
     * Request name that do not support aggregations
     */
    private const REQUEST_NAME_WITHOUT_AGGREGATION = 'graphql_product_search';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var SearchCriteriaInterfaceFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var SortOrderBuilder
     */
    private $sortBuilder;

    /**
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param Visibility $visibility
     */
    public function __construct(
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        ScopeConfigInterface $scopeConfig,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        Visibility $visibility
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->visibility = $visibility;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->sortBuilder = $sortBuilder;
    }

    /**
     * Build search criteria
     *
     * @param array $criteria
     * @return SearchCriteriaInterface
     */
    public function build(array $criteria): SearchCriteriaInterface
    {
        $includeAggregation = !empty($criteria['aggregations']) || $criteria['aggregations'] === null;

        $filters = $this->collectFilters($criteria, $includeAggregation);
        $sort = $this->collectSort($criteria);
        $requestName = $includeAggregation
            ? self::REQUEST_NAME_WITH_AGGREGATION
            : self::REQUEST_NAME_WITHOUT_AGGREGATION;

        $searchCriteria = $this->searchCriteriaFactory->create()
            ->setRequestName($requestName)
            ->setFilterGroups([$this->filterGroupBuilder->setFilters($filters)->create()])
            ->setSortOrders($sort)
            ->setCurrentPage($criteria['page']['currentPage'] ?? self::DEFAULT_CURRENT_PAGE)
            ->setPageSize($criteria['page']['pageSize'] ?? self::DEFAULT_PAGE_SIZE);

        return $searchCriteria;
    }

    /**
     * Get filter by visibility
     *
     * @param bool $isSearch
     * @param bool $isFilter
     *
     * @return Filter|null
     */
    private function getVisibilityFilter(bool $isSearch, bool $isFilter): ?Filter
    {
        if ($isFilter && $isSearch) {
            // Index already contains products filtered by visibility: catalog, search, both
            return null;
        }
        $visibilityIds = $isSearch
            ? $this->visibility->getVisibleInSearchIds()
            : $this->visibility->getVisibleInCatalogIds();

        return $this->buildFilter('visibility', $visibilityIds);
    }

    /**
     * Get price aggregation algorithm filter
     *
     * @return Filter|null
     */
    private function getPriceAggregationFilter(): ?Filter
    {
        $priceRangeCalculation = $this->scopeConfig->getValue(
            \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($priceRangeCalculation) {
            return $this->buildFilter('price_dynamic_algorithm', $priceRangeCalculation);
        }

        return null;
    }

    /**
     * Build filter
     *
     * @param string $field
     * @param mixed $value
     * @param string|null $condition
     *
     * @return Filter
     */
    private function buildFilter(string $field, $value, $condition = null): Filter
    {
        return $this->filterBuilder
            ->setField($field)
            ->setValue($value)
            ->setConditionType($condition)
            ->create();
    }

    /**
     * Collect filters from Search Criteria
     *
     * @param array $criteria
     * @param bool $includeAggregation
     * @return array
     */
    private function collectFilters(array $criteria, bool $includeAggregation): array
    {
        $filters = [];
        foreach ($criteria['filters'] as $attributeName => $conditions) {
            foreach ((array)$conditions as $condition => $value) {
                $filters[] = $this->buildFilter($attributeName, $value, $condition);
            }
        }
        $filters[] = $this->getVisibilityFilter(
            !empty($criteria['searchTerm']),
            !empty($criteria['filters'])
        );

        if ($criteria['searchTerm']) {
            $filters[] = $this->buildFilter('search_term', $criteria['searchTerm']);
        }

        if ($includeAggregation) {
            $filters[] = $this->getPriceAggregationFilter();
        }

        return \array_filter($filters);
    }

    /**
     * Collect sort criteria from Search Criteria
     *
     * @param array $criteria
     * @return array
     */
    private function collectSort(array $criteria): array
    {
        $sort = [];
        foreach ($criteria['sort'] as $attributeName => $sortDirection) {
            $sort[] = $this->sortBuilder->setField($attributeName)->setDirection($sortDirection)->create();
        }

        // add sort by document id for repeatability
        $sort['_id'] = $this->sortBuilder->setField('_id')->setDirection(SortOrder::SORT_ASC)->create();

        return $sort;
    }
}
