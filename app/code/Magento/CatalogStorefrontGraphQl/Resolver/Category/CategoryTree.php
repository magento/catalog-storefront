<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Category;

use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetResponseInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\CatalogStorefrontGraphQl\Model\FieldResolver;
use Magento\StorefrontGraphQl\Model\Query\ScopeProvider;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\StorefrontGraphQl\Model\ServiceInvoker;

/**
 * Category Tree resolver, used for GraphQL category data request processing.
 */
class CategoryTree implements BatchResolverInterface
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
     * @param FieldResolver $fieldResolver
     * @param ServiceInvoker $serviceInvoker
     * @param ScopeProvider $scopeProvider
     */
    public function __construct(
        FieldResolver $fieldResolver,
        ServiceInvoker $serviceInvoker,
        ScopeProvider $scopeProvider
    ) {
        $this->fieldResolver = $fieldResolver;
        $this->scopeProvider = $scopeProvider;
        $this->serviceInvoker = $serviceInvoker;
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
        $scopes = $this->scopeProvider->getScopes($context);
        $storefrontRequests = [];

        foreach ($requests as $request) {
            $storefrontRequest = ['scopes' => $scopes, 'store' => $scopes['store']];

            if ($field->getName() == 'category') {
                $categoryId = $request->getArgs()['id'] ?? null;
                if (is_null($categoryId)) {
                    $store = $context->getExtensionAttributes()->getStore();
                    $categoryId = (int)$store->getRootCategoryId();
                }

                $storefrontRequest['ids'] = [$categoryId];
                $storefrontRequest['attribute_codes'] = $this->fieldResolver->getSchemaTypeFields(
                    $request->getInfo(),
                    ['category']
                );
                $type = 'category';
            } elseif ($field->getName() == 'children') {
                $categoryIds = $request->getValue()['children'] ?? [];
                $storefrontRequest['ids'] = $categoryIds;
                $storefrontRequest['attribute_codes'] = $this->fieldResolver->getSchemaTypeFields(
                    $request->getInfo(),
                    ['children']
                );
                $type = 'children';
            } else {
                throw new \InvalidArgumentException(
                    'Category tree resolver support only category and children fields'
                );
            }

            $storefrontRequests[] = [
                'graphql_request' => $request,
                'storefront_request' => $storefrontRequest,
                'additional_info' => [
                    'type' => $type,
                    'category_ids' => $storefrontRequest['ids']
                ]
            ];
        }

        return $this->serviceInvoker->invoke(
            CatalogServerInterface::class,
            'GetCategories',
            $storefrontRequests,
            function (
                CategoriesGetResponseInterface $result,
                GraphQlInputException $e,
                BatchRequestItemInterface $request,
                array $additionalInfo
            ) use ($context) {
                $output = [];

                if (count($result->getItems()) != count($additionalInfo['category_ids'])) {
                    throw new GraphQlNoSuchEntityException(
                        __('Category doesn\'t exist')
                    );
                }

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
                        'product_count' => $item->getProductCount(),
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
                    if ($additionalInfo['type'] == 'category') {
                        return $itemOutput;
                    }
                    $output[] = $itemOutput;
                }
                return $output;
            }
        );

        return $this->serviceInvoker->invoke(
            CategoryInterface::class,
            'get',
            $storefrontRequests,
            function (
                CategoryResultContainerInterface $result,
                GraphQlInputException $e,
                BatchRequestItemInterface $request
            ) use ($context) {
                $errors = $result->getErrors() ?? $e->getErrors();
                if (!empty($errors)) {
                    //ad-hoc solution with __() as GraphQlInputException accepts Phrase in construct
                    throw new InputException(
                        __(\implode('; ', \array_map('\strval', $errors)))
                    );
                }
                $categoryId = $request->getArgs()['id']
                    ?? $context->getExtensionAttributes()->getStore()->getRootCategoryId();

                if (!isset($result->getCategories()[$categoryId])) {
                    throw new GraphQlNoSuchEntityException(__('Category doesn\'t exist'));
                }

                return $result->getCategories()[$categoryId];
            }
        );
    }
}
