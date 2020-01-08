<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Data;

/**
 * Data object interface for updated entities data collector
 */
interface UpdateEntitiesDataInterface
{
    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType(): string;

    /**
     * Get entity ID
     *
     * @return int
     */
    public function getEntityId(): int;

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Get entity data
     *
     * @return string
     */
    public function getEntityData(): string;
}
