<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CatalogStorefrontConnector\Model\Data\UpdateEntitiesDataInterfaceFactory;
use Magento\CatalogStorefrontConnector\Model\Data\UpdateEntitiesDataInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Message builder for publish entities update
 */
class EntitiesUpdateMessageBuilder
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var UpdateEntitiesDataInterfaceFactory
     */
    private $updateEntitiesDataInterfaceFactory;

    /**
     * @param UpdateEntitiesDataInterfaceFactory $updateEntitiesDataInterfaceFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        UpdateEntitiesDataInterfaceFactory $updateEntitiesDataInterfaceFactory,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->updateEntitiesDataInterfaceFactory = $updateEntitiesDataInterfaceFactory;
    }

    /**
     * Build message for entities update publishing process
     *
     * @param int $storeId
     * @param string $entityType
     * @param int $entityId
     * @param array $entityData
     * @return UpdateEntitiesDataInterface
     */
    public function build(
        int $storeId,
        string $entityType,
        int $entityId,
        array $entityData
    ): UpdateEntitiesDataInterface {
        /** @var UpdateEntitiesDataInterface $updateEntitiesData */
        $updateEntitiesData = $this->updateEntitiesDataInterfaceFactory->create(
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'store_id' => $storeId,
                'entity_data' => $this->serializer->serialize($entityData)
            ]
        );

        return $updateEntitiesData;
    }
}
