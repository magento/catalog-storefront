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
     * @var int
     */
    private $storeId;

    /**
     * @var int[]
     */
    private $entityIds;

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
}
