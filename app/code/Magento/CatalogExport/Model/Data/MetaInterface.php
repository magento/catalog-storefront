<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

/**
 * MetaData object interface for changed entities
 */
interface MetaInterface
{
    /**
     * Set scope for changed entities
     *
     * @param string|null $scope
     * @return void
     */
    public function setScope(?string $scope): void;

    /**
     * Get scope for changed entities
     *
     * @return string|null
     */
    public function getScope(): ?string;

    /**
     * Set changed entities event type
     *
     * @param string $eventType
     * @return void
     */
    public function setEventType(string $eventType): void;

    /**
     * Get changed entities event type
     *
     * @return string
     */
    public function getEventType(): string;
}
