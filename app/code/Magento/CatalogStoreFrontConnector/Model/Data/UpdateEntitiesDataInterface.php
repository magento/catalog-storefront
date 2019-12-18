<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model\Data;

/**
 * Data object interface for updated entities data collector
 */
interface UpdateEntitiesDataInterface
{
    /**
     * Set entity type
     *
     * @param string $entityType
     * @return void
     */
    public function setEntityType(string $entityType): void;

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType(): string;

    /**
     * Set entity ID
     *
     * @param int $entityId
     * @return void
     */
    public function setEntityId(int $entityId): void;

    /**
     * Get entity ID
     *
     * @return int
     */
    public function getEntityId(): int;

    /**
     * Set store ID
     *
     * @param int $storeId
     *
     * @return void
     */
    public function setStoreId(int $storeId): void;

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set entity data
     *
     * @param array $entityData
     * @return void
     */
    public function setEntityData(array $entityData): void;

    /**
     * Get entity data
     *
     * @return array
     */
    public function getEntityData(): array;
}
