<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Model;

use Magento\CatalogStoreFrontConnector\Model\Data\UpdateEntitiesDataInterface;

/**
 * Message builder for publish entities update
 */
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
     * Prepare message for entities update publishing process
     *
     * @param int $storeId
     * @param string $entityType
     * @param int $entityId
     * @param array $entityData
     * @return UpdateEntitiesDataInterface
     */
    public function prepareMessage(
        int $storeId,
        string $entityType,
        int $entityId,
        array $entityData
    ): UpdateEntitiesDataInterface {
        $this->updateEntitiesData->setStoreId($storeId);
        $this->updateEntitiesData->setEntityType($entityType);
        $this->updateEntitiesData->setEntityId($entityId);
        $this->updateEntitiesData->setEntityData($entityData);

        return $this->updateEntitiesData;
    }
}
