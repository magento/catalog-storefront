<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Data;

/**
 * Data object for updated entities collector
 */
class UpdatedEntitiesData implements UpdatedEntitiesDataInterface
{
    /**
     * Event types
     */
    const CATEGORIES_UPDATED_EVENT_TYPE = 'categories_updated';

    const CATEGORIES_DELETED_EVENT_TYPE = 'categories_deleted';

    const PRODUCTS_UPDATED_EVENT_TYPE = 'products_updated';

    const PRODUCTS_DELETED_EVENT_TYPE = 'products_deleted';

    /**
     * @var int|null
     */
    private $storeId;

    /**
     * @var int[]
     */
    private $entityIds;

    /**
     * @var string|null
     */
    private $eventType;

    /**
     * @inheritdoc
     */
    public function setStoreId(?int $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): ?int
    {
        return $this->storeId;
    }

    /**
     * @inheritdoc
     */
    public function setEntityIds(array $entityIds): void
    {
        $this->entityIds = $entityIds;
    }

    /**
     * @inheritdoc
     */
    public function getEntityIds(): array
    {
        return $this->entityIds;
    }

    /**
     * @inheritdoc
     */
    public function setEventType(?string $eventType): void
    {
        $this->eventType = $eventType;
    }

    /**
     * @inheritdoc
     */
    public function getEventType(): ?string
    {
        return $this->eventType;
    }
}
