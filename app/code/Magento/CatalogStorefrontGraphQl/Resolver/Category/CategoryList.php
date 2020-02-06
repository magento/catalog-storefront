<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Category;

use Magento\CatalogStorefrontApi\Api\CategoryInterface;
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
        foreach ($requests as $request) {
            $scopes = $this->scopeProvider->getScopes($context);
            $rootCategoryIds = [];

            $store = $context->getExtensionAttributes()->getStore();
            if (!isset($request->getArgs()['filters'])) {
                $rootCategoryIds[] = (int)$store->getRootCategoryId();
            } else {
                try {
                    $categoryCollection = $this->collectionFactory->create();
                    $this->categoryFilter->applyFilters($request->getArgs(), $categoryCollection, $store);
                    foreach ($categoryCollection as $category) {
                        $rootCategoryIds[] = (int)$category->getId();
                    }
                } catch (InputException $e) {
                    // ad-hoc solution to handle case with invalid filter
                    $batchResponse = new BatchResponse();
                    $batchResponse->addResponse($request, []);
                    return $batchResponse;
                }
            }

            $storefrontRequest = [
                'ids' => $rootCategoryIds,
                'scopes' => $scopes,
                'attributes' => $this->fieldResolver->getSchemaTypeFields($request->getInfo(), ['categoryList']),
            ];
            $storefrontRequests[] = [
                'graphql_request' => $request,
                'storefront_request' => $storefrontRequest
            ];
        }

        return $this->serviceInvoker->invoke(
            CategoryInterface::class,
            'get',
            $storefrontRequests,
            function (
                CategoryResultContainerInterface $result
            ) {
                $errors = $result->getErrors();
                if (!empty($errors)) {
                    //ad-hoc solution with __() as GraphQlInputException accepts Phrase in construct
                    throw new InputException(
                        __(\implode('; ', \array_map('\strval', $errors)))
                    );
                }

                return $result->getCategories();
            }
        );
    }
}
