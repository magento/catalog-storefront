<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver;

use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefrontApi\Api\CatalogInterface;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterfaceFactory;
use Magento\CatalogStorefrontGraphQl\Resolver\Product\OutputFormatter;
use Magento\CatalogStorefrontGraphQl\Resolver\Product\RequestBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;

/**
 * Products field resolver, used for GraphQL request processing.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Products implements BatchResolverInterface
{
    /**
     * @var \Magento\StorefrontGraphQl\Model\ServiceInvoker
     */
    private $serviceInvoker;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;


    /**
     * @param \Magento\StorefrontGraphQl\Model\ServiceInvoker $serviceInvoker
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(
        \Magento\StorefrontGraphQl\Model\ServiceInvoker $serviceInvoker,
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
            $searchRequest = $this->requestBuilder->buildRequest($context, $request);
            $searchRequest['storefront_request']['attribute_codes']
                = $searchRequest['storefront_request']['attributes'];
            $searchRequest['storefront_request']['store']
                = $searchRequest['storefront_request']['scopes']['store'] ?? null;

            $storefrontRequests[] = $searchRequest;
        }

        return $this->serviceInvoker->invoke(
            CatalogServerInterface::class,
            'GetProducts',
            $storefrontRequests,
            new OutputFormatter
        );
    }
}
