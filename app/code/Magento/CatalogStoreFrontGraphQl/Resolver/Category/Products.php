<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver\Category;

use Magento\CatalogStoreFrontGraphQl\Model\ProductSearch;
use Magento\CatalogStoreFrontGraphQl\Resolver\Product\RequestBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\CatalogProductApi\Api\ProductSearchInterface;
use Magento\StoreFrontGraphQl\Model\ServiceInvoker;

/**
 * Category products resolver, used by GraphQL endpoints to retrieve products assigned to a category
 */
class Products implements BatchResolverInterface
{
    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var ServiceInvoker
     */
    private $serviceInvoker;

    /**
     * @var ProductSearch
     */
    private $productSearch;

    /**
     * @param RequestBuilder $requestBuilder
     * @param ServiceInvoker $serviceInvoker
     * @param ProductSearch $productSearch
     */
    public function __construct(
        RequestBuilder $requestBuilder,
        ServiceInvoker $serviceInvoker,
        ProductSearch $productSearch
    ) {
        $this->serviceInvoker = $serviceInvoker;
        $this->requestBuilder = $requestBuilder;
        $this->productSearch = $productSearch;
    }

    /**
     * @inheritdoc
     *
     * @param ContextInterface $context GraphQL context.
     * @param Field $field Field metadata.
     * @param BatchRequestItemInterface[] $requests Requests to the field.
     * @return BatchResponse Aggregated response.
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $storefrontRequests = [];
        foreach ($requests as $request) {
            $filter = [
                'category_id' => [
                    'eq' => (int)$request->getValue()['id']
                ]
            ];
            $request = $this->requestBuilder->buildRequest($context, $request, $filter);
            $storefrontRequests[] = $this->productSearch->search($request);
        }

        return $this->serviceInvoker->invoke(
            ProductSearchInterface::class,
            'search',
            $storefrontRequests,
            new \Magento\CatalogStoreFrontGraphQl\Resolver\Product\OutputFormatter
        );
    }
}
