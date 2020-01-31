<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogStoreFrontGraphQl\Model;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\SearchResponseBuilder;

/**
 * Performs search by searchCriteria in search engine.
 */
class Search
{
    /**
     * @var Builder
     */
    private $requestBuilder;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var SearchResponseBuilder
     */
    private $searchResponseBuilder;

    /**
     * @param Builder $requestBuilder
     * @param ScopeResolverInterface $scopeResolver
     * @param SearchEngineInterface $searchEngine
     * @param SearchResponseBuilder $searchResponseBuilder
     */
    public function __construct(
        Builder $requestBuilder,
        ScopeResolverInterface $scopeResolver,
        SearchEngineInterface $searchEngine,
        SearchResponseBuilder $searchResponseBuilder
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->scopeResolver = $scopeResolver;
        $this->searchEngine = $searchEngine;
        $this->searchResponseBuilder = $searchResponseBuilder;
    }

    /**
     * @inheritdoc
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $this->requestBuilder->setRequestName($searchCriteria->getRequestName());

        $scope = $this->scopeResolver->getScope()->getId();
        $this->requestBuilder->bindDimension('scope', $scope);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $this->addFieldToFilter($filter->getField(), $filter->getValue(), $filter->getConditionType());
            }
        }

        $this->requestBuilder->setFrom($searchCriteria->getCurrentPage() * $searchCriteria->getPageSize());
        $this->requestBuilder->setSize($searchCriteria->getPageSize());

        /**
         * This added in Backward compatibility purposes.
         * Temporary solution for an existing API of a fulltext search request builder.
         * It must be moved to different API.
         * Scope to split Search request builder API in MC-16461.
         */
        if (method_exists($this->requestBuilder, 'setSort')) {
            $this->requestBuilder->setSort($searchCriteria->getSortOrders());
        }
        $request = $this->requestBuilder->create();
        $searchResponse = $this->searchEngine->search($request);

        return $this->searchResponseBuilder->build($searchResponse)
            ->setSearchCriteria($searchCriteria);
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param mixed $value
     * @param mixed $condition
     */
    private function addFieldToFilter($field, $value, $condition): void
    {
        if (\in_array($condition, ['from', 'to'], true)) {
            $this->requestBuilder->bind("{$field}.{$condition}", $value);
        } else {
            $this->requestBuilder->bind($field, $value);
        }
    }
}
