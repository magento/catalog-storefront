<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogExport\Event\Data\Entity;

/**
 * Execute consumer event
 */
interface ConsumerEventInterface
{
    /**
     * Execute consumers by ids for specified scope
     *
     * @param Entity[] $entities
     * @param string $scope
     *
     * @return void
     */
    public function execute(array $entities, string $scope): void;
}
