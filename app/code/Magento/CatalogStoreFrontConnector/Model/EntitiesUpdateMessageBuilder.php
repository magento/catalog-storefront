<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model;

class EntitiesUpdateMessageBuilder
{
    /**
     * @var UpdateEntitiesDataInterface
     */
    private $updateEntitiesData;

    /**
     * @param UpdateEntitiesDataInterface $updateEntitiesData
     */
    public function __construct(
        UpdateEntitiesDataInterface $updateEntitiesData
    ) {
        $this->updateEntitiesData = $updateEntitiesData;
    }

    /**
     * @param int $storeId
     * @param string $entityType
     * @param int $entityId
     * @param array $entityData
     * @return UpdateEntitiesDataInterface
     */
    public function prepareMessage($storeId, string $entityType, int $entityId, array $entityData)
    {

        $this->updateEntitiesData->setStoreId($storeId);
        $this->updateEntitiesData->setEntityType($entityType);
        $this->updateEntitiesData->setEntityId($entityId);
        $this->updateEntitiesData->setEntityData($entityData);

        return $this->updateEntitiesData;
    }
}
