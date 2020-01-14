<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\MessageBus;

use Magento\CatalogProduct\Model\Storage\Client\CommandInterface;
use Magento\CatalogProduct\Model\Storage\State;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

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
     * @param CommandInterface $storage
     * @param State $storageState
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandInterface $storage,
        State $storageState,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->storageState = $storageState;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Process
     *
     * @param CatalogItemMessage[] $entities
     * @return void
     */
    public function process(array $entities): void
    {
        try {
            $dataPerType = $this->collectDataByEntityTypeAnsScope($entities);
            $this->saveToStorage($dataPerType);
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Collect catalog data. Structure by entity type and scope
     *
     * @param array $entities
     * @return array
     */
    private function collectDataByEntityTypeAnsScope(array $entities): array
    {
        $dataPerType = [];
        foreach ($entities as $entity) {
            $entityData = $this->serializer->unserialize($entity->getEntityData());
            $entityData['id'] = $entity->getEntityId();
            $entityData['store_id'] = $entity->getStoreId();
            $dataPerType[$entity->getEntityType()][$entity->getStoreId()][] = $entityData;
        }

        return $dataPerType;
    }

    /**
     * Save catalog data to the internal storage
     *
     * @param array $dataPerType
     * @throws \Magento\Framework\Exception\BulkException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function saveToStorage(array $dataPerType): void
    {
        foreach ($dataPerType as $entityType => $dataPerStore) {
            foreach ($dataPerStore as $storeId => $data) {
                $sourceName = $this->storageState->getCurrentDataSourceName([$storeId, $entityType]);
                // TODO: MC-30401
                // $this->dataDefinition->createEntity($sourceName, $entityType, []);
                $this->storage->bulkInsert($sourceName, $entityType, $data);
            }
        }
    }
}
