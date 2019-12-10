<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreFrontGraphQl\Model\Query\ScopeProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Interface for providing declared scope information
 */
interface ScopeInterface
{
    /**
     * Get scope value
     *
     * @param ContextInterface $context
     * @return mixed
     * @throws GraphQlNoSuchEntityException
     */
    public function getValue(ContextInterface $context);
}
