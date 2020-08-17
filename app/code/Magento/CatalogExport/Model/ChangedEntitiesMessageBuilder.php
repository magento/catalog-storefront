<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExport\Model\Data\ChangedEntitiesInterface;
use Magento\CatalogExport\Model\Data\DataInterface;
use Magento\CatalogExport\Model\Data\MetaInterface;

/**
 * Class that builds queue message for changed entities
 */
class ChangedEntitiesMessageBuilder
{
    /**
     * @var ChangedEntitiesInterface
     */
    private $changedEntities;

    /**
     * @var MetaInterface
     */
    private $meta;

    /**
     * @var DataInterface
     */
    private $data;

    /**
     * @param ChangedEntitiesInterface $changedEntities
     * @param MetaInterface $meta
     * @param DataInterface $data
     */
    public function __construct(
        ChangedEntitiesInterface $changedEntities,
        MetaInterface $meta,
        DataInterface $data
    ) {
        $this->changedEntities = $changedEntities;
        $this->meta = $meta;
        $this->data = $data;
    }

    /**
     * Build message object
     *
     * @param int[] $entityIds
     * @param string $eventType
     * @param string|null $scope
     * @return \Magento\CatalogExport\Model\Data\ChangedEntitiesInterface
     */
    public function build(array $entityIds, string $eventType, ?string $scope): ChangedEntitiesInterface
    {
        $this->meta->setEventType($eventType);
        $this->meta->setScope($scope);
        $this->data->setIds($entityIds);
        $this->changedEntities->setMeta($this->meta);
        $this->changedEntities->setData($this->data);
        return $this->changedEntities;
    }
}
