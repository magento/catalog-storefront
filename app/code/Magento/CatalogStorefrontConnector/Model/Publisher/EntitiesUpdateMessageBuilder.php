<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CatalogStorefrontMessageBus\Message\CatalogItemFactory;
use Magento\CatalogStorefrontMessageBus\Message\CatalogItem;
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
     * @var CatalogItemFactory
     */
    private $catalogItemFactory;

    /**
     * @param CatalogItemFactory $catalogItemFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CatalogItemFactory $catalogItemFactory,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->catalogItemFactory = $catalogItemFactory;
    }

    /**
     * Build message for entities update publishing process
     *
     * @param int $storeId
     * @param string $entityType
     * @param int $entityId
     * @param array $entityData
     * @return CatalogItem
     */
    public function build(
        int $storeId,
        string $entityType,
        int $entityId,
        array $entityData
    ): CatalogItem {
        /** @var CatalogItem $catalogItem */
        $catalogItem = $this->catalogItemFactory->create(
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'store_id' => $storeId,
                'entity_data' => $this->serializer->serialize($entityData)
            ]
        );

        return $catalogItem;
    }
}
