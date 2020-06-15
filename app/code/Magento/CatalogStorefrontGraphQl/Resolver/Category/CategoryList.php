<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Category;

use Magento\CatalogGraphQl\Model\Category\CategoryFilter;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryInterface;
use Magento\CatalogStorefrontGraphQl\Model\FieldResolver;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\StorefrontGraphQl\Model\Query\ScopeProvider;
use Magento\StorefrontGraphQl\Model\ServiceInvoker;

/**
 * Category List resolver, used for GraphQL category data request processing.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryList implements BatchResolverInterface
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
     * @var ServiceInvoker
     */
    private $serviceInvoker;

    /**
     * @var CategoryFilter
     */
    private $categoryFilter;

    /**
     * @param FieldResolver $fieldResolver
     * @param ServiceInvoker $serviceInvoker
     * @param ScopeProvider $scopeProvider
     * @param CategoryFilter $categoryFilter
     */
    public function __construct(
        FieldResolver $fieldResolver,
        ServiceInvoker $serviceInvoker,
        ScopeProvider $scopeProvider,
        CategoryFilter $categoryFilter
    ) {
        $this->fieldResolver = $fieldResolver;
        $this->scopeProvider = $scopeProvider;
        $this->serviceInvoker = $serviceInvoker;
        $this->categoryFilter = $categoryFilter;
    }

    /**
     * @inheritdoc
     * @param ContextInterface $context GraphQL context.
     * @param Field $field Field metadata.
     * @param BatchRequestItemInterface[] $requests Requests to the field.
     * @return BatchResponse Aggregated response.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $storefrontRequests = [];
        $batchResponse = null;
        try {
            foreach ($requests as $request) {
                $storefrontRequests[] = $this->prepareStorefrontRequest($request, $context, $field);
            }
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        // ad-hoc solution to handle case with invalid filter
        if (null !== $batchResponse) {
            return $batchResponse;
        }

        return $this->serviceInvoker->invoke(
            CatalogServerInterface::class,
            'GetCategories',
            $storefrontRequests,
            function (
                CategoriesGetResponseInterface $result,
                $graphQlException,
                $graphQlRequest,
                $additionalInfo
            ) {
                $output = [];
                foreach ($result->getItems() as $item) {
                    $output[] = $this->prepareOutput($item);
                }
                if ($additionalInfo['type'] === 'categories') {
                    $output = [
                        'items' => $output,
                        'total_count' => $additionalInfo['total_count'] ?? count($output),
                        'page_info' => $additionalInfo['page_info'] ?? []
                    ];
                }
                return $output;
            }
        );
    }

    /**
     * Prepare storefront request based on requested arguments and fields
     *
     * @param BatchRequestItemInterface $request
     * @param ContextInterface $context
     * @param Field $field
     * @throws GraphQlInputException
     * @throws InputException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     * @return array
     */
    private function prepareStorefrontRequest(
        BatchRequestItemInterface $request,
        ContextInterface $context,
        Field $field
    ): array {
        if (isset($request->getArgs()['pageSize']) && $request->getArgs()['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        if (isset($request->getArgs()['currentPage']) && $request->getArgs()['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        $scopes = $this->scopeProvider->getScopes($context);
        $categoryIds = [];

        $store = $context->getExtensionAttributes()->getStore();

        if (!isset($request->getArgs()['filters'])) {
            $categoryIds[] = (int)$store->getRootCategoryId();
        } else {
            $data = $this->categoryFilter->getResult($request->getArgs(), $store);
            $categoryIds = $data['category_ids'];
        }

        $attributeCodes = $field->getName() === 'categoryList'
            ? $this->fieldResolver->getSchemaTypeFields($request->getInfo(), ['categoryList'])
            : $this->fieldResolver->getSchemaTypeFields($request->getInfo(), ['categories'], 'items');

        $storefrontRequest = [
            'ids' => $categoryIds,
            'scopes' => $scopes,
            'store' => $store->getId(),
            'attribute_codes' => $attributeCodes,
        ];
        return [
            'graphql_request' => $request,
            'storefront_request' => $storefrontRequest,
            'additional_info' => [
                'type' => $field->getName() === 'categoryList' ? 'categoryList' : 'categories',
                'page_info' => $data['page_info'] ?? [],
                'total_count' => $data['total_count'] ?? 0,
            ]
        ];
    }

    /**
     * Prepare output based on category item
     *
     * @param CategoryInterface $item
     * @return array
     */
    private function prepareOutput(CategoryInterface $item): array
    {
        $itemOutput = [
            'id' => $item->getId(),
            'entity_id' => $item->getId(),
            'path' => $item->getPath(),
            'url_key' => $item->getUrlKey(),
            'image' => $item->getImage(),
            'description' => $item->getDescription(),
            'name' => $item->getName(),
            'available_sort_by' => $item->getAvailableSortBy(),
            'canonical_url' => empty($item->getCanonicalUrl()) ? null : $item->getCanonicalUrl(),
            'children_count' => $item->getChildrenCount(),
            'default_sort_by' => $item->getDefaultSortBy(),
            'include_in_menu' => $item->getIncludeInMenu(),
            'is_active' => $item->getIsActive(),
            'is_anchor' => $item->getIsAnchor(),
            'level' => $item->getLevel(),
            'position' => $item->getPosition(),
            'url_path' => $item->getUrlPath(),
            'display_mode' => $item->getDisplayMode(),
            'children' => $item->getChildren(),
            'meta_title' => $item->getMetaTitle(),
            'meta_description' => $item->getMetaDescription(),
            'meta_keywords' => $item->getMetaKeywords(),
            'product_count' => $item->getProductCount()
        ];

        foreach ($item->getBreadcrumbs() as $offset => $breadcrumb) {
            $itemOutput['breadcrumbs'][$offset] = [
                "category_id" => $breadcrumb->getCategoryId(),
                "category_url_key" => $breadcrumb->getCategoryUrlKey(),
                "category_url_path" => $breadcrumb->getCategoryUrlPath(),
                "category_level" => $breadcrumb->getCategoryLevel(),
                "category_name" => $breadcrumb->getCategoryName(),
            ];
        }

        return $itemOutput;
    }
}
