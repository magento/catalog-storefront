<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Model;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;

/**
 * Products search
 *
 * Ad-hoc solution. Product search executed in Magento Monolith until Catalog Storefront Search service implemented.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductSearch
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Search
     */
    private $search;

    /**
     * @var LayerBuilder
     */
    private $layerBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Search $search
     * @param LayerBuilder $layerBuilder
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Search $search,
        LayerBuilder $layerBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->search = $search;
        $this->layerBuilder = $layerBuilder;
    }

    /**
     * Performs products search for given request.
     *
     * @param array $request
     * @return array
     */
    public function search(array $request): array
    {
        $storefrontRequest = $request['storefront_request'];

        $searchCriteria = $this->searchCriteriaBuilder->build($storefrontRequest);

        $showLayeredNavigation = !empty($storefrontRequest['aggregations'])
            || $storefrontRequest['aggregations'] === null;
        [$totalCount, $productIds, $aggregations] = $this->searchProducts(
            $searchCriteria,
            $showLayeredNavigation
        );

        $request['storefront_request']['ids'] = $productIds;

        $currentPage = $searchCriteria->getCurrentPage();
        $maxPages = $this->getTotalPages($searchCriteria, $totalCount);
        $metaInfo = [
            'page_size' => $searchCriteria->getPageSize(),
            'current_page' => $searchCriteria->getCurrentPage(),
            'total_pages' => $this->getTotalPages($searchCriteria, $totalCount),
            'total_count' => $totalCount,
            // for backward compatibility: support "filters" field
            'layer_type' => $storefrontRequest['searchTerm']
                ? LayerResolver::CATALOG_LAYER_SEARCH
                : LayerResolver::CATALOG_LAYER_CATEGORY
        ];

        $request['additional_info']['errors'] = [];
        if ($currentPage > $maxPages && $totalCount > 0) {
            $request['additional_info']['errors'][] = __(
                'currentPage value %1 specified is greater than the %2 page(s) available.',
                $currentPage,
                $maxPages
            );
        }
        $request['additional_info']['meta_info'] = $metaInfo;
        $request['additional_info']['aggregations'] = $showLayeredNavigation
            ? $this->layerBuilder->build($aggregations, (int)$storefrontRequest['scopes']['store'])
            : [];

        //TODO: Remove dependency on CatalogGraphQl module
        $buckets = [];
        foreach ($request['additional_info']['aggregations'] as $bucket) {
            $values = [];
            foreach ($bucket['options'] as $option) {
                $values[] = new \Magento\Framework\Search\Response\Aggregation\Value($option['label'], [
                    'count' => $option['count'],
                    'value' => $option['value']
                ]);
            }
            $attributeCode = $bucket['attribute_code'] == 'category_id'
                ? 'category'
                : $bucket['attribute_code'];
            $bucket = new \Magento\Framework\Search\Response\Bucket($attributeCode, $values);
            $buckets[$attributeCode] = $bucket;

        }
        $searchResult = new \Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult([
            'searchAggregation' => new \Magento\Framework\Search\Response\Aggregation($buckets)
        ]);
        $request['additional_info']['search_result'] = $searchResult;

        return $request;
    }

    /**
     * Search product using Search API.
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
