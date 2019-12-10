<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreFrontGraphQl\Model\Query\ScopeProvider;

use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Store scope
 */
class StoreScope implements ScopeInterface
{
    /**
     * @inheritDoc
     * @return string
     * @throws GraphQlNoSuchEntityException
     */
    public function getValue(ContextInterface $context): string
    {
        /** @var \Magento\GraphQl\Model\Query\ContextExtensionInterface $extensionAttributes */
        $extensionAttributes = $context->getExtensionAttributes();
        $store = $extensionAttributes->getStore();
        if (null === $store) {
            throw new GraphQlNoSuchEntityException(
                __('There is no store in extension attributes')
            );
        }
        return (string)$store->getId();
    }
}
