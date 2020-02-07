<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontGraphQl\Model\Query;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Provide scope data to resolvers
 */
class ScopeProvider
{
    /**
     * @var \Magento\StorefrontGraphQl\Model\Query\ScopeProvider\ScopeInterface[]
     */
    private $scopes;

    /**
     * @var array
     */
    private $storedScopes = [];

    /**
     * @param \Magento\StorefrontGraphQl\Model\Query\ScopeProvider\ScopeInterface[] $scopes
     */
    public function __construct(
        array $scopes
    ) {
        $this->scopes = $scopes;
    }

    /**
     * Get "name -> value" pairs for declared scopes
     *
     * @param ContextInterface $context
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getScopes(ContextInterface $context): array
    {
        if (empty($this->storedScopes)) {
            $scopes = [];
            foreach ($this->scopes as $scopeName => $scopeClass) {
                $scopes[$scopeName] = $scopeClass->getValue($context);
            }
            $this->storedScopes = $scopes;
        }

        return $this->storedScopes;
    }
}
