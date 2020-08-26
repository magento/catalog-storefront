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
     * @param ChangedEntitiesInterfaceFactory $changedEntitiesFactory
     * @param MetaInterfaceFactory $metaFactory
     * @param DataInterfaceFactory $dataFactory
     */
    public function __construct(
        ChangedEntitiesInterfaceFactory $changedEntitiesFactory,
        MetaInterfaceFactory $metaFactory,
        DataInterfaceFactory $dataFactory
    ) {
        $this->changedEntitiesFactory = $changedEntitiesFactory;
        $this->metaFactory = $metaFactory;
        $this->dataFactory = $dataFactory;
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
        $meta = $this->metaFactory->create(
            [
                'scope' => $scope,
                'eventType' => $eventType
            ]
        );

        $data = $this->dataFactory->create(
            [
                'entityIds' => $entityIds
            ]
        );

        return $this->changedEntitiesFactory->create(
            [
                'meta' => $meta,
                'data' => $data
            ]
        );
    }
}
