<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model\Data;

/**
 * Data object for collect updated entities data
 */
class UpdateEntitiesData implements UpdateEntitiesDataInterface
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var int
     */
    private $entityId;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var array
     */
    private $entityData;

    /**
     * @inheritdoc
     */
    public function setEntityType(string $entityType): void
    {
        $this->entityType = $entityType;
    }

    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @inheritdoc
     */
    public function setEntityId(int $entityId): void
    {
        $this->entityId = $entityId;
    }

    /**
     * @inheritdoc
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @inheritdoc
     */
    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): int
    {
        return $this->storeId;
    }

    /**
     * @inheritdoc
     */
    public function setEntityData(array $entityData): void
    {
        $this->entityData = $entityData;
    }

    /**
     * @inheritdoc
     */
    public function getEntityData(): array
    {
        return $this->entityData;
    }
}
