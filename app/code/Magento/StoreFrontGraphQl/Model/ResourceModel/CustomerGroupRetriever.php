<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreFrontGraphQl\Model\ResourceModel;

/**
 * Resource model for getting customer group ID
 */
class CustomerGroupRetriever
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get customer group ID by customer ID
     *
     * @param int $customerId
     * @return int $customerGroupId
     */
    public function getCustomerGroupId(int $customerId): int
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from($this->resource->getTableName('customer_entity'), 'group_id')
            ->where('entity_id = ?', $customerId);
        $customerGroupId = $connection->fetchOne($select);

        return (int)$customerGroupId;
    }
}
