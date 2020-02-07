<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontGraphQl\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Group as CustomerGroup;
use Magento\CustomerGraphQl\Model\Context\AddUserInfoToContext;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\StorefrontGraphQl\Model\ResourceModel\CustomerGroupRetriever;

/**
 * Add 'customer_group_id' to context.
 */
class AddCustomerGroupToContext
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CustomerGroupRetriever
     */
    private $userGroupRetriever;

    /**
     * @param UserContextInterface $userContext
     * @param CustomerGroupRetriever $userGroupRetriever
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerGroupRetriever $userGroupRetriever
    ) {
        $this->userContext = $userContext;
        $this->userGroupRetriever = $userGroupRetriever;
    }

    /**
     * Add 'customer_group_id' to context.
     *
     * @param AddUserInfoToContext $subject
     * @param ContextParametersInterface $contextParameters
     * @return ContextParametersInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(AddUserInfoToContext $subject, ContextParametersInterface $contextParameters)
    {
        $currentUserId = $this->userContext->getUserId();
        if (null !== $currentUserId) {
            $currentUserId = (int)$currentUserId;
        }
        $currentUserType = $this->userContext->getUserType();
        if (null !== $currentUserType) {
            $currentUserType = (int)$currentUserType;
        }

        $customerGroupId = ($this->userContext::USER_TYPE_GUEST !== $currentUserType)
            ? $this->userGroupRetriever->getCustomerGroupId($currentUserId)
            : CustomerGroup::NOT_LOGGED_IN_ID;
        $contextParameters->addExtensionAttribute(
            'customer_group_id',
            $customerGroupId
        );

        return $contextParameters;
    }
}
