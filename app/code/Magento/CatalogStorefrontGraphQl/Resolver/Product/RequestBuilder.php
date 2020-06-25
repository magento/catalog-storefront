<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Product;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequestInterface;
use Magento\CatalogStorefrontGraphQl\Model\FieldResolver;
use Magento\StorefrontGraphQl\Model\Query\ScopeProvider;
use Magento\CatalogStorefrontGraphQl\Model\ProductSearch;

/**
 * Build request for storefront service.
 *
 * Return array of request [[GraphQl request, Storefront request], ...]
 */
class RequestBuilder
{
    /**
     * @var FieldResolver
     */
    private $fieldResolver;

    /**
     * @var ScopeProvider
     */
    private $scopeProvider;

    /**
     * @var ProductSearch
     */
    private $productSearch;

    /**
     * @param FieldResolver $fieldResolver
     * @param ScopeProvider $scopeProvider
     * @param ProductSearch $productSearch
     */
    public function __construct(
        FieldResolver $fieldResolver,
        ScopeProvider $scopeProvider,
        ProductSearch $productSearch
    ) {
        $this->fieldResolver = $fieldResolver;
        $this->scopeProvider = $scopeProvider;
        $this->productSearch = $productSearch;
    }

    /**
     * Build GraphQL request
     *
     * @param ContextInterface $context
     * @param BatchRequestItemInterface|ResolveRequestInterface $request
     * @param array $filter
     * @param array $sort
     * @return array
     * @throws GraphQlInputException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     */
    public function buildRequest(ContextInterface $context, $request, array $filter = [], array $sort = []): array
    {
        $args = $request->getArgs();
        $info = $request->getInfo();

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        $filter = \array_merge($args['filter'] ?? [], $filter);
        if (!$filter && !isset($args['search'])) {
            throw new GraphQlInputException(
                __("'search' or 'filter' input argument is required.")
            );
        }

        $sort = \array_merge($args['sort'] ?? [], $sort);

        $attributes = $this->fieldResolver->getSchemaTypeFields($info, ['products'], 'items');
        $aggregations = $this->fieldResolver->getSchemaTypeFields($info, ['products'], 'aggregations');
        $filters = $this->fieldResolver->getSchemaTypeFields($info, ['products'], 'filters');
        $totalCount = $this->fieldResolver->getSchemaTypeFields($info, ['products'], 'totalCount');

        $metaInfo = [];
        if (!empty($totalCount)) {
            $metaInfo['totalCount'] = true;
        }
        // null - retrieve all aggregations, [] - do not return aggregations
        $aggregations = (!empty($aggregations) || !empty($filters)) ? null : [];

        $page = [
            'currentPage' => $args['currentPage'],
            'pageSize' => $args['pageSize']
        ];
        $this->addDefaultSortOrder($args);

        // Required for GraphQL framework
        $attributes[] = 'type_id';

        $storefrontRequest = [
            'searchTerm' => $args['search'] ?? null,
            'filters' => $filter,
            'page' => $page,
            'scopes' => $this->scopeProvider->getScopes($context),
            'attributes' => $attributes,
            'sort' => $sort,
            'aggregations' => $aggregations,
            'metaInfo' => $metaInfo,
        ];

        return $this->productSearch->search(
            [
                'graphql_request' => $request,
                'storefront_request' => $storefrontRequest,
            ]
        );
    }

    /**
     * Add default sort order if it was not specified.
     * For "search" scenario: sort by "relevance" DESC
     * For "filter (category)" scenario: sort by "category position" ASC
     *
     * @param array $request
     * @return void
     */
    private function addDefaultSortOrder(&$request): void
    {
        if (empty($request['sort'])) {
            $isSearch = !empty($request['search']);
            $sortField = $isSearch ? 'relevance' : EavAttributeInterface::POSITION;
            $sortDirection = $isSearch ? SortOrder::SORT_DESC : SortOrder::SORT_ASC;
            $request['sort'][$sortField] = $sortDirection;
        }
    }
}
