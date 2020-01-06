<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogStorefrontConnector\Model\Data\UpdateEntitiesDataInterfaceFactory;
use Magento\CatalogStorefrontConnector\Model\Data\UpdateEntitiesDataInterface;
use Magento\Framework\Json\Encoder;

/**
 * Message builder for publish entities update
 */
class EntitiesUpdateMessageBuilder
{
    /**
     * @var Encoder
     */
    private $encoder;
    /**
     * @var UpdateEntitiesDataInterfaceFactory
     */
    private $updateEntitiesDataInterfaceFactory;

    /**
     * @param UpdateEntitiesDataInterfaceFactory $updateEntitiesDataInterfaceFactory
     * @param Encoder $encoder
     */
    public function __construct(
        UpdateEntitiesDataInterfaceFactory $updateEntitiesDataInterfaceFactory,
        Encoder $encoder
    ) {
        $this->encoder = $encoder;
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
                'entityType' => $entityType,
                'entityId' => $entityId,
                'storeId' => $storeId,
                'entityData' => $this->encoder->encode($entityData)
            ]
        );

        return $updateEntitiesData;
    }
}
