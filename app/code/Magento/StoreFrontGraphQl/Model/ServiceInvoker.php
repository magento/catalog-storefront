<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreFrontGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\ObjectManagerInterface;

/**
 * Invoke Storefront service and map response to GraphQL request
 */
class ServiceInvoker
{
    /**
     * GraphQL request identifier
     */
    private const GRAPHQL_REQUEST = 'graphql_request';

    /**
     * StoreFront request identifier
     */
    private const STOREFRONT_REQUEST = 'storefront_request';

    /**
     * Meta info error identifier
     */
    private const ADDITIONAL_INFO = 'additional_info';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ArgumentResolver $argumentResolver
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ArgumentResolver $argumentResolver
    ) {
        $this->objectManager = $objectManager;
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * Call service and map results to GraphQL response
     *
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @param array $storefrontRequests
     * @param callable|null $formatter
     * @return BatchResponse
     * @throws \ReflectionException
     * @throws GraphQlInputException
     */
    public function invoke(
        string $serviceClassName,
        string $serviceMethodName,
        array $storefrontRequests,
        callable $formatter = null
    ): BatchResponse {
        $this->validateRequests($storefrontRequests);

        $service = $this->objectManager->get($serviceClassName);
        $criteriaClassName = $this->argumentResolver->getArgumentClassName($serviceClassName, $serviceMethodName);
        $serviceArguments = [];

        foreach ($storefrontRequests as $request) {
            $serviceArguments[] = $this->objectManager->create($criteriaClassName, $request[self::STOREFRONT_REQUEST]);
        }
        $serviceResponse = $service->$serviceMethodName($serviceArguments);

        $formatter = \is_callable($formatter)
            ? $formatter
            : function ($result) {
                return $result;
            };

        $batchResponse = new BatchResponse();
        $graphQlException = new GraphQlInputException(
            __('Error happened during service call "%1"', $serviceClassName . '::' . $serviceMethodName)
        );
        foreach ($serviceResponse as $responseNumber => $response) {
            $batchResponse->addResponse(
                $storefrontRequests[$responseNumber][self::GRAPHQL_REQUEST],
                $formatter(
                    $response,
                    $graphQlException,
                    $storefrontRequests[$responseNumber][self::GRAPHQL_REQUEST],
                    $storefrontRequests[$responseNumber][self::ADDITIONAL_INFO] ?? []
                )
            );
        }

        if ($graphQlException->getErrors()) {
            throw $graphQlException;
        }
        return $batchResponse;
    }

    /**
     * Validate input requests
     *
     * @param array $storefrontRequests
     * @throws \InvalidArgumentException
     */
    private function validateRequests(array $storefrontRequests): void
    {
        foreach ($storefrontRequests as $request) {
            if (!$request[self::GRAPHQL_REQUEST] instanceof BatchRequestItemInterface) {
                throw new \InvalidArgumentException(
                    'Storefront request is invalid. First request should be instance of '
                    . BatchRequestItemInterface::class
                );
            }
            if (!\is_array($request[self::STOREFRONT_REQUEST])) {
                throw new \InvalidArgumentException(
                    'Storefront request is invalid. Second request should be an array'
                );
            }
        }
    }
}
