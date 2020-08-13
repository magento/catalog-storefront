<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExport\Model\Data\ChangedEntitiesDataInterface;

/**
 * Reindex message builder
 */
class ChangedEntitiesMessageBuilder
{
    /**
     * @var ChangedEntitiesDataInterface
     */
    private $changedEntitiesData;

    /**
     * @param ChangedEntitiesDataInterface $changedEntitiesData
     */
    public function __construct(
        ChangedEntitiesDataInterface $changedEntitiesData
    ) {
        $this->changedEntitiesData = $changedEntitiesData;
    }

    /**
     * @param int[] $entityIds
     * @param string $eventType
     * @param string|null $scope
     * @return ChangedEntitiesDataInterface
     */
    public function build(array $entityIds, string $eventType, ?string $scope): ChangedEntitiesDataInterface
    {
        $this->changedEntitiesData->setEntityIds($entityIds);
        $this->changedEntitiesData->setEventType($eventType);
        $this->changedEntitiesData->setScope($scope);

        return $this->changedEntitiesData;
    }
}
