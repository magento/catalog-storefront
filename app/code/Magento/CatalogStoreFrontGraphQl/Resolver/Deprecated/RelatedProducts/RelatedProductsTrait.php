<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver\Deprecated\RelatedProducts;

use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\CatalogStoreFrontGraphQl\Model\ProductModelHydrator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequestFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\RelatedProductGraphQl\Model\DataProvider\RelatedProductDataProvider;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
trait RelatedProductsTrait
{
    /**
     * @var ProductModelHydrator
     */
    private $productModelHydrator;

    /**
     * @var ResolveRequestFactory
     */
    private $resolveRequestFactory;

    /**
     * @var ProductFieldsSelector
     */
    private $productFieldsSelector;

    /**
     * @var RelatedProductDataProvider
     */
    private $relatedProductDataProvider;

    /**
     * @param ProductFieldsSelector $productFieldsSelector
     * @param RelatedProductDataProvider $relatedProductDataProvider
     * @param ProductDataProvider $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductModelHydrator $productModelHydrator
     * @param ResolveRequestFactory $resolveRequestFactory
     */
    public function __construct(
        ProductFieldsSelector $productFieldsSelector,
        RelatedProductDataProvider $relatedProductDataProvider,
        ProductDataProvider $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductModelHydrator $productModelHydrator,
        ResolveRequestFactory $resolveRequestFactory
    ) {
        parent::__construct(
            $productFieldsSelector,
            $relatedProductDataProvider,
            $productDataProvider,
            $searchCriteriaBuilder
        );
        $this->productModelHydrator = $productModelHydrator;
        $this->resolveRequestFactory = $resolveRequestFactory;
        $this->productFieldsSelector = $productFieldsSelector;
        $this->relatedProductDataProvider = $relatedProductDataProvider;
    }

    /**
     * @inheritdoc
     *
     * @param ContextInterface $context
     * @param Field $field
     * @param array $requests
     * @return BatchResponse
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function resolve(
        ContextInterface $context,
        Field $field,
        array $requests
    ): BatchResponse {
        $requestsOriginal = $requests;

        foreach ($requests as $key => $request) {
            $newValue = $this->productModelHydrator->hydrate($request->getValue());

            $requests[$key] = $this->resolveRequestFactory->create(
                [
                    'field' => $request->getField(),
                    'context' => $request->getContext(),
                    'info' => $request->getInfo(),
                    'value' => $newValue,
                    'args' => $request->getArgs(),
                ]
            );
        }

        $fields = $this->getFields($requests);
        if (empty($fields)) {
            return $this->getRelationsOnly($requests, $requestsOriginal);
        }

        $resolvedRequests = parent::resolve($context, $field, $requests);

        $response = new BatchResponse();
        foreach ($requests as $key => $request) {
            $result = $resolvedRequests->findResponseFor($request);
            $response->addResponse($requestsOriginal[$key], $result);
        }

        return $response;
    }

    /**
     * Get list of fields from request.
     *
     * @param array $requests
     * @return array
     */
    private function getFields(array $requests): array
    {
        $fields = [];
        /** @var \Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            $fields[] = $this->productFieldsSelector->getProductFieldsFromInfo($request->getInfo(), $this->getNode());
        }
        $fields = array_unique(array_merge(...$fields));

        return $fields;
    }

    /**
     * Get list of related products.
     *
     * @param $requests
     * @param $requestsOriginal
     * @return BatchResponse
     */
    private function getRelationsOnly($requests, $requestsOriginal): BatchResponse
    {
        $products = [];
        /** @var \Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            $products[] = $request->getValue()['model'];
        }

        // TODO: handle ad-hoc solution MC-29791
        // TODO: determine if we need add relations to $response or return $relations
        $relations = $this->relatedProductDataProvider->getRelations($products, $this->getLinkType());
        $response = new BatchResponse();
        foreach ($requests as $key => $request) {
            $response->addResponse($requestsOriginal[$key], $relations);
        }
        return $response;
    }
}
