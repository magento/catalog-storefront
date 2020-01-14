<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use \Magento\CatalogStorefrontConnector\Model\Publisher\CatalogItemMessageFactory;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Message builder for publish entities update
 */
class CatalogItemMessageBuilder
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CatalogItemMessageFactory
     */
    private $catalogItemFactory;

    /**
     * @param CatalogItemMessageFactory $catalogItemFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CatalogItemMessageFactory $catalogItemFactory,
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
     * @return CatalogItemMessage
     */
    public function build(
        int $storeId,
        string $entityType,
        int $entityId,
        array $entityData
    ): CatalogItemMessage {
        /** @var CatalogItemMessage $catalogItem */
        $catalogItem = $this->catalogItemFactory->create(
            [
                'entityType' => $entityType,
                'entityId' => $entityId,
                'storeId' => $storeId,
                'entityData' => $this->serializer->serialize($entityData)
            ]
        );

        return $catalogItem;
    }
}
