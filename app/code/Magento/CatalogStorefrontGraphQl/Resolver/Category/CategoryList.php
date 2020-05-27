<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Category;

use Magento\CatalogStorefrontApi\Api\CategoryInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryResultContainerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\CatalogStorefrontGraphQl\Model\FieldResolver;
use Magento\StorefrontGraphQl\Model\Query\ScopeProvider;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\StorefrontGraphQl\Model\ServiceInvoker;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\Category\CategoryFilter;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;

;

/**
 * Category List resolver, used for GraphQL category data request processing.
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
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param FieldResolver $fieldResolver
     * @param ServiceInvoker $serviceInvoker
     * @param ScopeProvider $scopeProvider
     * @param CategoryFilter $categoryFilter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        FieldResolver $fieldResolver,
        ServiceInvoker $serviceInvoker,
        ScopeProvider $scopeProvider,
        CategoryFilter $categoryFilter,
        CollectionFactory $collectionFactory
    ) {
        $this->fieldResolver = $fieldResolver;
        $this->scopeProvider = $scopeProvider;
        $this->serviceInvoker = $serviceInvoker;
        $this->categoryFilter = $categoryFilter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     * @param ContextInterface $context GraphQL context.
     * @param Field $field Field metadata.
     * @param BatchRequestItemInterface[] $requests Requests to the field.
     * @return BatchResponse Aggregated response.
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $storefrontRequests = [];
        $batchResponse = null;
        try {
            foreach ($requests as $request) {
                $scopes = $this->scopeProvider->getScopes($context);
                $categoryIds = [];

                $store = $context->getExtensionAttributes()->getStore();

                if (!isset($request->getArgs()['filters'])) {
                    $categoryIds[] = (int)$store->getRootCategoryId();
                } else {
                    $data = $this->categoryFilter->getResult($request->getArgs(), $store);
                    $categoryIds = $data['category_ids'];
                }

                $storefrontRequest = [
                    'ids' => $categoryIds,
                    'scopes' => $scopes,
                    'store' => $store->getId(),
                    'attribute_codes' => $this->fieldResolver->getSchemaTypeFields($request->getInfo(), ['categoryList']),
                ];

                $storefrontRequests[] = [
                    'graphql_request' => $request,
                    'storefront_request' => $storefrontRequest
                ];
            }
        } catch (InputException $e) {
            throw $e;
            $batchResponse = $batchResponse ?? new BatchResponse();
            $batchResponse->addResponse($request, []);
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
                CategoriesGetResponseInterface $result
            ) {
//                $errors = $result->getErrors();
//                if (!empty($errors)) {
//                    //ad-hoc solution with __() as GraphQlInputException accepts Phrase in construct
//                    throw new InputException(
//                        __(\implode('; ', \array_map('\strval', $errors)))
//                    );
//                }
                $output = [];
                foreach ($result->getItems() as $item) {
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

                    $output[] = $itemOutput;
                }
                return $output;
            }
        );
    }
}
