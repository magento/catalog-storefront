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
    }

    /**
     * @inheritdoc
     *
     * @param ContextInterface $context
     * @param Field $field
     * @param array $requests
     * @return BatchResponse
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
        $resolvedRequests = parent::resolve($context, $field, $requests);

        $response = new BatchResponse();
        foreach ($requests as $key => $request) {
            $result = $resolvedRequests->findResponseFor($request);
            $response->addResponse($requestsOriginal[$key], $result);
        }

        return $response;
    }
}
