<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Api\Data;

class EntityRequestData implements EntityRequestDataInterface
{
    /**
     * @var int
     */
    private $entityId;

    /**
     * @var string[]
     */
    private $attributeCodes;

    /**
     * @inheritdoc
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @inheritdoc
     */
    public function setEntityId(int $entityId): void
    {
        $this->entityId = $entityId;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeCodes(): array
    {
        return (array)$this->attributeCodes;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeCodes(array $attributeCodes): void
    {
        $this->attributeCodes = $attributeCodes;
    }
}
