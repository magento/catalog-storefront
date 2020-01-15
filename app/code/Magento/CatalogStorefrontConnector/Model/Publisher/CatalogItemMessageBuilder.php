<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

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
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Build message for entities update publishing process
     *
     * @param int $storeId
     * @param string $entityType
     * @param int $entityId
     * @param array $entityData
     * @return string
     */
    public function build(
        int $storeId,
        string $entityType,
        int $entityId,
        array $entityData
    ): string {
        return $this->serializer->serialize(
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'store_id' => $storeId,
                'entity_data' => $entityData,
            ]
        );
    }
}
