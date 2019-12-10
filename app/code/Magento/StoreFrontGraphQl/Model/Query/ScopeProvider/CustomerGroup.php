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
 * Customer group scope
 */
class CustomerGroup implements ScopeInterface
{
    /**
     * @inheritDoc
     *
     * @return string
     * @throws GraphQlNoSuchEntityException
     */
    public function getValue(ContextInterface $context): string
    {
        $extensionAttributes = $context->getExtensionAttributes();
        $customerGroupId = $extensionAttributes->getCustomerGroupId();
        if (null === $customerGroupId) {
            throw new GraphQlNoSuchEntityException(
                __('Customer group id wasn\'t found')
            );
        }
        return (string)$customerGroupId;
    }
}
