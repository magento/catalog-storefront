<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

/**
 * Execute consumer event
 */
interface ConsumerEventInterface

{
    /**
     * @param array $entityIds
     * @param string $scope
     * @return void
     */
    public function execute(array $entityIds, string $scope): void;
}
