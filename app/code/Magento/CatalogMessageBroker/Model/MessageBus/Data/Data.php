<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Data;

/**
 * Data object for changed entities
 */
class Data implements DataInterface
{
    /**
     * @var int[]
     */
    private $entityIds;

    /**
     * @param array $entityIds
     */
    public function __construct(array $entityIds)
    {
        $this->entityIds = $entityIds;
    }

    /**
     * @inheritdoc
     */
    public function getIds(): array
    {
        return $this->entityIds;
    }
}
