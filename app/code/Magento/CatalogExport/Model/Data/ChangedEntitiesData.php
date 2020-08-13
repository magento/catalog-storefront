<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

/**
 * Data object for changed entities collector
 */
class ChangedEntitiesData implements ChangedEntitiesDataInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $meta;

    /**
     * @ingeritdoc
     */
    public function setEntityIds(array $entityIds): void
    {
        $this->data['ids'] = $entityIds;
    }

    /**
     * @ingeritdoc
     */
    public function getEntityIds(): array
    {
        return $this->data['ids'];
    }

    /**
     * @ingeritdoc
     */
    public function setScope(?string $scope): void
    {
        $this->meta['scope'] = $scope;
    }

    /**
     * @ingeritdoc
     */
    public function getScope(): ?string
    {
        return $this->meta['scope'];
    }

    /**
     * @ingeritdoc
     */
    public function setEventType(string $eventType): void
    {
        $this->meta['type'] = $eventType;
    }

    /**
     * @ingeritdoc
     */
    public function getEventType(): string
    {
        return $this->meta['type'];
    }
}
