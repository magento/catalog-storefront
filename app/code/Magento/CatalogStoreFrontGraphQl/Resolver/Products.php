<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver;

use Magento\CatalogStoreFrontGraphQl\Resolver\Product\OutputFormatter;
use Magento\CatalogStoreFrontGraphQl\Resolver\Product\RequestBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\CatalogProductApi\Api\ProductSearchInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\StoreFrontGraphQl\Model\ServiceInvoker as ServiceInvokerAlias;
use Magento\CatalogStoreFrontGraphQl\Model\ProductSearch;

/**
 * Products field resolver, used for GraphQL request processing.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Products implements BatchResolverInterface
{
    /**
     * @var ServiceInvokerAlias
     */
    private $serviceInvoker;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var ProductSearch
     */
    private $productSearch;

    /**
     * @param ServiceInvokerAlias $serviceInvoker
     * @param RequestBuilder $requestBuilder
     * @param ProductSearch $productSearch
     */
    public function __construct(
        ServiceInvokerAlias $serviceInvoker,
        RequestBuilder $requestBuilder,
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
            $request = $this->requestBuilder->buildRequest($context, $request);
            $storefrontRequests[] = $this->productSearch->search($request);
        }
        return $this->serviceInvoker->invoke(
            ProductSearchInterface::class,
            'search',
            $storefrontRequests,
            new OutputFormatter
        );
    }
}
