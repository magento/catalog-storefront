<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Deprecated;

use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\CatalogStorefrontGraphQl\Model\ProductModelHydrator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\Resolver\ResolveRequestFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\TargetRuleGraphQl\Model\ProductList\TargetRuleProductList;

/**
 * Class for request values hydration by entity model
 */
class TargetRuleProducts extends \Magento\TargetRuleGraphQl\Model\Resolver\Batch\TargetRuleProducts
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
     * @param ProductModelHydrator $productModelHydrator
     * @param ProductFieldsSelector $productFieldsSelector
     * @param ProductDataProvider $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TargetRuleProductList $targetRuleProductList
     */
    public function __construct(
        ProductModelHydrator $productModelHydrator,
        ProductFieldsSelector $productFieldsSelector,
        ProductDataProvider $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TargetRuleProductList $targetRuleProductList,
        ResolveRequestFactory $resolveRequestFactory
    ) {
        $this->productModelHydrator = $productModelHydrator;
        $this->resolveRequestFactory = $resolveRequestFactory;
        parent::__construct(
            $productFieldsSelector,
            $productDataProvider,
            $searchCriteriaBuilder,
            $targetRuleProductList
        );
    }

    /**
     * @inheritdoc
     *
     * Add 'model' to $value in requests
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return array
     */
    public function applyTargetRuleResponses(
        ContextInterface $context,
        array $requests,
        BatchResponse $response,
        string $node,
        int $linkType
    ): BatchResponse {
        foreach ($requests as $key => $request) {
            $newValue = $this->productModelHydrator->hydrate($request->getValue());

            $newRequest = $this->resolveRequestFactory->create(
                [
                    'field' => $request->getField(),
                    'context' => $request->getContext(),
                    'info' => $request->getInfo(),
                    'value' => $newValue,
                    'args' => $request->getArgs(),
                ]
            );
            $requests[$key] = $newRequest;
        }
        return parent::applyTargetRuleResponses(
            $context,
            $requests,
            $response,
            $node,
            $linkType
        );
    }
}
