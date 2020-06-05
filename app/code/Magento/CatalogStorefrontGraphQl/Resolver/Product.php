<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver;

use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResultInterface;
use Magento\CatalogStorefrontGraphQl\Model\FieldResolver;
use Magento\CatalogStorefrontGraphQl\Resolver\Product\OutputFormatter;
use Magento\CatalogStorefrontGraphQl\Resolver\Product\RequestBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\StorefrontGraphQl\Model\Query\ScopeProvider;

/**
 * Product field resolver, used for GraphQL request processing.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product implements BatchResolverInterface
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
     * @var FieldResolver
     */
    private $fieldResolver;
    /**
     * @var ScopeProvider
     */
    private $scopeProvider;


    /**
     * @param \Magento\StorefrontGraphQl\Model\ServiceInvoker $serviceInvoker
     * @param RequestBuilder $requestBuilder
     * @param FieldResolver $fieldResolver
     * @param ScopeProvider $scopeProvider
     */
    public function __construct(
        \Magento\StorefrontGraphQl\Model\ServiceInvoker $serviceInvoker,
        RequestBuilder $requestBuilder,
        FieldResolver $fieldResolver,
        ScopeProvider $scopeProvider
    ) {
        $this->serviceInvoker = $serviceInvoker;
        $this->requestBuilder = $requestBuilder;
        $this->fieldResolver = $fieldResolver;
        $this->scopeProvider = $scopeProvider;
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
            $productId = $request->getValue()['product'] ?? $request->getValue()['entity_id'] ?? null;
            if (!$productId) {
                throw new \InvalidArgumentException('Product id is missing in request');
            }
            $attributes = $this->fieldResolver->getSchemaTypeFields(
                $request->getInfo(),
                ['product']
            );

            $searchRequest = ['graphql_request' => $request];
            $searchRequest['storefront_request']['attribute_codes'] = $attributes;
            $searchRequest['storefront_request']['ids'] = [$productId];

            $scopes = $this->scopeProvider->getScopes($context);
            $searchRequest['storefront_request']['store'] = $scopes['store'];

            $storefrontRequests[] = $searchRequest;
        }

        return $this->serviceInvoker->invoke(
            CatalogServerInterface::class,
            'GetProducts',
            $storefrontRequests,
            function (
                ProductsGetResultInterface $result,
                GraphQlInputException $e,
                BatchRequestItemInterface $request,
                array $additionalInfo = []
            ) {
                $formatter = new OutputFormatter;
                $result = $formatter($result, $e, $request, $additionalInfo);
                return $result['items'][0] ?? [];
            }
        );
    }
}
