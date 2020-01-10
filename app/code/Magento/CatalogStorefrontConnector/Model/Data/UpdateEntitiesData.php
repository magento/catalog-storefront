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
     * @param string $entity_type
     * @param int $entity_id
     * @param int $store_id
     * @param string $entity_data
     * @see \Magento\Framework\Webapi\ServiceInputProcessor::process for exalanation snake_case argument naming
     */
    public function __construct(string $entity_type, int $entity_id, int $store_id, string $entity_data)
    {
        $this->entityType = $entity_type;
        $this->entityId = $entity_id;
        $this->storeId = $store_id;
        $this->entityData = $entity_data;
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
