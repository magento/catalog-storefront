<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExport\Model\Data\ChangedEntitiesInterfaceFactory;
use Magento\CatalogExport\Model\Data\DataInterfaceFactory;
use Magento\CatalogExport\Model\Data\MetaInterfaceFactory;
use Magento\CatalogExport\Model\Data\EntityFactory;
use Magento\CatalogExport\Model\Data\ChangedEntitiesInterface;

/**
 * Class that builds queue message for changed entities
 */
class ChangedEntitiesMessageBuilder
{
    /**
     * @var ChangedEntitiesInterfaceFactory
     */
    private $changedEntitiesFactory;

    /**
     * @var MetaInterfaceFactory
     */
    private $metaFactory;

    /**
     * @var DataInterfaceFactory
     */
    private $dataFactory;

    /**
     * @var EntityFactory
     */
    private $entityFactory;

    /**
     * @param ChangedEntitiesInterfaceFactory $changedEntitiesFactory
     * @param MetaInterfaceFactory $metaFactory
     * @param DataInterfaceFactory $dataFactory
     * @param EntityFactory $entityFactory
     */
    public function __construct(
        ChangedEntitiesInterfaceFactory $changedEntitiesFactory,
        MetaInterfaceFactory $metaFactory,
        DataInterfaceFactory $dataFactory,
        EntityFactory $entityFactory
    ) {
        $this->changedEntitiesFactory = $changedEntitiesFactory;
        $this->metaFactory = $metaFactory;
        $this->dataFactory = $dataFactory;
        $this->entityFactory = $entityFactory;
    }

    /**
     * Build message object
     *
     * @param string $eventType
     * @param array $entities
     * @param string $scope
     *
     * @return \Magento\CatalogExport\Model\Data\ChangedEntitiesInterface
     */
    public function build(string $eventType, array $entities, string $scope): ChangedEntitiesInterface
    {
        $meta = $this->metaFactory->create();
        $meta->setScope($scope);
        $meta->setEventType($eventType);

        $entitiesArray = [];
        foreach ($entities as $entityData) {
            $entity = $this->entityFactory->create();
            $entity->setEntityId($entityData['entity_id']);
            $entity->setAttributes($entityData['attributes'] ?? []);

            $entitiesArray[] = $entity;
        }

        $data = $this->dataFactory->create();
        $data->setEntities($entitiesArray);

        $changedEntities = $this->changedEntitiesFactory->create();
        $changedEntities->setMeta($meta);
        $changedEntities->setData($data);

        return $changedEntities;
    }
}
