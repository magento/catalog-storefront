<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogExport\Event\Data;

/**
 * Data object for entity data
 */
class Entity
{
    /**
     * @var int|string
     */
    private $entityId;

    /**
     * @var string[]
     */
    private $attributes;

    /**
     * Get entity id.
     *
     * @return int|string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set entity id.
     *
     * @param int|string $entityId
     *
     * @return void
     */
    public function setEntityId($entityId): void
    {
        $this->entityId = $entityId;
    }

    /**
     * Get entity attributes.
     *
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set entity attributes.
     *
     * @param string[] $attributes
     *
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }
}
