<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model;

interface UpdateEntitiesDataInterface
{
    /**
     * @param string $entityType
     * @return void
     */
    public function setEntityType(string $entityType);

    /**
     * @return string
     */
    public function getEntityType(): string;

    /**
     * @param int $entityId
     * @return void
     */
    public function setEntityId(int $entityId);

    /**
     * @return string
     */
    public function getEntityId(): int;

    /**
     * @param int $storeId
     *
     * @return void
     */
    public function setStoreId(int $storeId);

    /**
     * Get store ID for products reindex
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param array $entityData
     * @return void
     */
    public function setEntityData(array $entityData);

    /**
     * @return array
     */
    public function getEntityData(): array;
}
