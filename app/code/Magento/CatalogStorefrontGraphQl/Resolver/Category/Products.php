<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Category;

use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontGraphQl\Resolver\Product\OutputFormatter;
use Magento\CatalogStorefrontGraphQl\Resolver\Product\RequestBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\CatalogStorefrontApi\Api\ProductInterface;
use Magento\StorefrontGraphQl\Model\ServiceInvoker;

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
     * @param RequestBuilder $requestBuilder
     * @param ServiceInvoker $serviceInvoker
     */
    public function __construct(RequestBuilder $requestBuilder, ServiceInvoker $serviceInvoker)
    {
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
            $filter = [
                'category_id' => [
                    'eq' => (int)$request->getValue()['id']
                ]
            ];
            $storefrontRequest = $this->requestBuilder->buildRequest($context, $request, $filter);
            $storefrontRequest['storefront_request']['attribute_codes']
                = $storefrontRequest['storefront_request']['attributes'];
            $storefrontRequest['storefront_request']['store']
                = $storefrontRequest['storefront_request']['scopes']['store'] ?? null;

            $storefrontRequests[] = $storefrontRequest;
            //var_dump($storefrontRequest['storefront_request']);
        }
        //exit;

        return $this->serviceInvoker->invoke(
            CatalogServerInterface::class,
            'GetProducts',
            $storefrontRequests,
            new OutputFormatter
        );
        return $this->serviceInvoker->invoke(
            ProductInterface::class,
            'get',
            $storefrontRequests,
            new \Magento\CatalogStorefrontGraphQl\Resolver\Product\OutputFormatter
        );
    }
}
