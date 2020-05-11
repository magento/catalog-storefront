<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Data;

/**
 * Data object interface for updated entities collector
 */
interface UpdatedEntitiesDataInterface
{
    /**
     * Get event type
     *
     * @param string $eventType
     * @return void
     */
    public function setType(string $eventType): void;

    /**
     * Get event type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Set store ID for updated entities
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void;

    /**
     * Get store ID for updated entities
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set updated entities IDs
     *
     * @param int[] $entityIds
     * @return void
     */
    public function setEntityIds(array $entityIds): void;

    /**
     * Get updated entities IDs
     *
     * @return int[]
     */
    public function getEntityIds(): array;
}
