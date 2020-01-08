<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Data;

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
     * @var string
     */
    private $entityData;

    /**
     * @param string $entityType
     * @param int $entityId
     * @param int $storeId
     * @param string $entityData
     */
    public function __construct(string $entityType, int $entityId, int $storeId, string $entityData)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->storeId = $storeId;
        $this->entityData = $entityData;
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
    public function getEntityId(): int
    {
        return $this->entityId;
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
    public function getEntityData(): string
    {
        return $this->entityData;
    }
}
