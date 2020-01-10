<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\MessageBus;

use Magento\CatalogProduct\Model\Storage\Client\CommandInterface;
use Magento\CatalogProduct\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogProduct\Model\Storage\State;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
// TODO: new connector module between Magento and StoreFront
use Magento\CatalogStorefrontConnector\Model\Data\UpdateEntitiesDataInterface;

/**
 * Consumer for store data to data storage.
 */
class Consumer
{
    /**
     * @var CommandInterface
     */
    private $storage;

    /**
     * @var State
     */
    private $storageState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var DataDefinitionInterface
     */
    private $dataDefinition;

    /**
     * @param CommandInterface $storage
     * @param DataDefinitionInterface $dataDefinition
     * @param State $storageState
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandInterface $storage,
        DataDefinitionInterface $dataDefinition,
        State $storageState,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->storageState = $storageState;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->dataDefinition = $dataDefinition;
    }

    /**
     * Process
     *
     * @param \Magento\CatalogStorefrontConnector\Model\Data\UpdateEntitiesDataInterface[] $entities
     * @return void
     * @throws \Throwable
     */
    public function process(array $entities): void
    {
        try {
            // collect data by entity type and store id
            $dataPerType = [];
            foreach ($entities as $entity) {
                $this->validateEntityType($entity);
                $entityData = $this->serializer->unserialize($entity->getEntityData());
                $entityData['id'] = $entity->getEntityId();
                $entityData['store_id'] = $entity->getStoreId();
                $dataPerType[$entity->getEntityType()][$entity->getStoreId()][] = $entityData;
            }

            // save data to storage
            foreach ($dataPerType as $entityType => $dataPerStore) {
                foreach ($dataPerStore as $storeId => $data) {
                    $sourceName = $this->storageState->getCurrentDataSourceName([$storeId, $entityType]);
                    // TODO: fix error "mapping type is missing;"
                    // $this->dataDefinition->createEntity($sourceName, $entityType, []);
                    $this->storage->bulkInsert($sourceName, $entityType, $data);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Check entity type before put data to storage
     *
     * @param \Magento\CatalogStorefrontConnector\Model\Data\UpdateEntitiesDataInterface $entity
     */
    private function validateEntityType(UpdateEntitiesDataInterface $entity): void
    {
        if (!\in_array($entity->getEntityType(), [State::ENTITY_TYPE_PRODUCT, State::ENTITY_TYPE_CATEGORY], true)) {
            throw new \LogicException(\sprintf('Entity type "%s" is not supported', $entity->getEntityType()));
        }
    }
}
