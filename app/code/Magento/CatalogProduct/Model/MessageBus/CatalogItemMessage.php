<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\MessageBus;

use Magento\CatalogProduct\Model\Storage\Client\Config\Product;

/**
 * DTO represent catalog item data stored in Message Bus
 */
class CatalogItemMessage
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
     * @see \Magento\Framework\Webapi\ServiceInputProcessor::process for explanation snake_case argument naming
     * @throws \LogicException
     */
    public function __construct(string $entity_type, int $entity_id, int $store_id, string $entity_data)
    {
        $this->validateEntityType($entity_type);
        $this->entityType = $entity_type;
        $this->entityId = $entity_id;
        $this->storeId = $store_id;
        $this->entityData = $entity_data;
    }

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * Get entity ID
     *
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->storeId;
    }

    /**
     * Get entity data
     *
     * @return string
     */
    public function getEntityData(): string
    {
        return $this->entityData;
    }


    /**
     * Check entity type before put data to storage
     *
     * @param $entityType
     * @throws \LogicException
     */
    private function validateEntityType($entityType): void
    {
        if (!\in_array($entityType, [Product::ENTITY_NAME], true)) {
            throw new \LogicException(\sprintf('Entity type "%s" is not supported', $entityType));
        }
    }

}
