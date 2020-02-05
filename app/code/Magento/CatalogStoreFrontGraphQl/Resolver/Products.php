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
use Magento\CatalogStorefrontApi\Api\ProductSearchInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;

/**
 * Products field resolver, used for GraphQL request processing.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Products implements BatchResolverInterface
{
    /**
     * @var \Magento\StoreFrontGraphQl\Model\ServiceInvoker
     */
    private $serviceInvoker;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @param \Magento\StoreFrontGraphQl\Model\ServiceInvoker $serviceInvoker
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(
        \Magento\StoreFrontGraphQl\Model\ServiceInvoker $serviceInvoker,
        RequestBuilder $requestBuilder
    ) {
        $this->serviceInvoker = $serviceInvoker;
        $this->requestBuilder = $requestBuilder;
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
            $storefrontRequests[] = $this->requestBuilder->buildRequest($context, $request);
        }
        return $this->serviceInvoker->invoke(
            ProductSearchInterface::class,
            'search',
            $storefrontRequests,
            new OutputFormatter
        );
    }
}
