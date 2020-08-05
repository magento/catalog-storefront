<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Deprecated;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as CatalogProductDataProvider;
use Magento\CatalogStorefront\DataProvider\ProductDataProvider;
use Magento\CatalogStorefrontGraphQl\Model\ProductModelHydrator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequestFactory;
use Magento\TargetRuleGraphQl\Model\ProductList\TargetRuleProductList;

/**
 * Class for overriding target rule product list resolver
 */
class TargetRuleProducts extends \Magento\TargetRuleGraphQl\Model\Resolver\Batch\TargetRuleProducts
{
    /**
     * @var ProductFieldsSelector
     */
    private $productFieldsSelector;

    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var TargetRuleProductList
     */
    private $targetRuleProductList;

    /**
     * @var ProductModelHydrator
     */
    private $productModelHydrator;

    /**
     * @param ProductFieldsSelector $productFieldsSelector
     * @param CatalogProductDataProvider $catalogProductDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TargetRuleProductList $targetRuleProductList
     * @param ProductDataProvider $productDataProvider
     * @param ProductModelHydrator $productModelHydrator
     */
    public function __construct(
        ProductFieldsSelector $productFieldsSelector,
        CatalogProductDataProvider $catalogProductDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TargetRuleProductList $targetRuleProductList,
        ProductDataProvider $productDataProvider,
        ProductModelHydrator $productModelHydrator
    ) {
        parent::__construct(
            $productFieldsSelector,
            $catalogProductDataProvider,
            $searchCriteriaBuilder,
            $targetRuleProductList
        );
        $this->productFieldsSelector = $productFieldsSelector;
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->targetRuleProductList = $targetRuleProductList;
        $this->productModelHydrator = $productModelHydrator;
    }

    /**
     * Add target rule responses.
     *
     * @param ContextInterface $context
     * @param BatchRequestItemInterface[] $requests
     * @param BatchResponse $resultResponse
     * @param string $node
     * @param int $linkType
     * @return BatchResponse
     * @throws LocalizedException
     */
    public function applyTargetRuleResponses(
        ContextInterface $context,
        array $requests,
        BatchResponse $resultResponse,
        string $node,
        int $linkType
    ): BatchResponse {
        $products = [];
        $fields = [];

        foreach ($requests as $request) {
            $hydratedValue = $this->productModelHydrator->hydrate($request->getValue());
            if (empty($hydratedValue['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            $products[] = $hydratedValue['model'];
            $fields[] = $this->productFieldsSelector->getProductFieldsFromInfo($request->getInfo(), $node);
        }
        $fields = array_unique(array_merge(...$fields));

        $related = $this->findTargetRuleRelations($context, $products, $fields, $linkType);

        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $result = $related[$product->getId()] ?? [];

            if (!empty($result)) {
                $response = $resultResponse->findResponseFor($request);
                if (is_array($response) && !empty($response)) {
                    foreach ($response as $responseItem) {
                        $result[] = $responseItem;
                    }
                }
                $resultResponse->addResponse($request, $result);
            }
        }

        return $resultResponse;
    }

    /**
     * Find related products by target rule configuration.
     *
     * @param ContextInterface $context
     * @param ProductInterface[] $products
     * @param string[] $loadAttributes
     * @param int $linkType
     * @return array
     */
    private function findTargetRuleRelations(
        ContextInterface $context,
        array $products,
        array $loadAttributes,
        int $linkType
    ): array {
        $relations = $this->getRelations($context, $products, $linkType);

        if (!$relations) {
            return [];
        }
        $relatedIds = array_map(
            function ($relatedProducts) {
                return array_keys($relatedProducts);
            },
            array_values($relations)
        );
        $relatedIds = array_unique(array_merge(...$relatedIds));
        //Loading products data with attributes.
        $this->searchCriteriaBuilder->addFilter('entity_id', $relatedIds, 'in');
        $relatedSearchResult = $this->productDataProvider->fetch(
            $relatedIds,
            $loadAttributes,
            ['store' => $context->getExtensionAttributes()->getStore()->getId()]
        );
        //Filling related products map.
        /** @var ProductInterface[] $relatedProducts */
        $relatedProducts = [];
        foreach ($relatedSearchResult as $item) {
            $relatedProducts[$item['id']] = $item;
        }

        //Matching products with related products.
        $relationsData = [];
        foreach ($relations as $productId => $relatedItems) {
            $relationsData[$productId] = array_map(
                function ($id) use ($relatedProducts) {
                    return $relatedProducts[$id];
                },
                array_keys($relatedItems)
            );
        }

        return $relationsData;
    }

    /**
     * Retrieve product relations.
     *
     * @param ContextInterface $context
     * @param ProductInterface[] $products
     * @param int $linkType
     * @return ProductInterface[]
     * @throws LocalizedException
     */
    private function getRelations($context, $products, $linkType): array
    {
        $relations = [];
        foreach ($products as $product) {
            $relations[$product->getId()] =
                $this->targetRuleProductList->getTargetRuleProducts($context, $product, $linkType);
        }
        return $relations;
    }
}
