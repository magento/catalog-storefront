<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Data;

/**
 * MetaData object interface for changed entities
 */
interface MetaInterface
{
    /**
     * Get scope for changed entities
     *
     * @return string|null
     */
    public function getScope(): ?string;

    /**
     * Get changed entities event type
     *
     * @return string
     */
    public function getEventType(): string;
}
