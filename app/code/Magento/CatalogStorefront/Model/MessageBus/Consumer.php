<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\MessageBus;

use Magento\CatalogStorefront\Model\Storage\Client\CommandInterface;
use Magento\CatalogStorefront\Model\Storage\State;
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
     * @var CatalogItemMessageBuilder
     */
    private $catalogItemMessageBuilder;

    /**
     * @param CommandInterface $storage
     * @param State $storageState
     * @param CatalogItemMessageBuilder $catalogItemMessageBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandInterface $storage,
        State $storageState,
        CatalogItemMessageBuilder $catalogItemMessageBuilder,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->storageState = $storageState;
        $this->logger = $logger;
        $this->catalogItemMessageBuilder = $catalogItemMessageBuilder;
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
     * @param array $messages
     * @return array
     */
    private function collectDataByEntityTypeAnsScope(array $messages): array
    {
        $dataPerType = [];
        $messages = array_merge(...$messages);
        foreach ($messages as $message) {
            $entity = $this->catalogItemMessageBuilder->build($message);
            $entityData = $entity->getEntityData();
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

                $this->logger->debug(
                    \sprintf('Save to storage "%s" %s record(s)', $sourceName, count($data)),
                    ['verbose' => $data]
                );

                $this->storage->bulkInsert($sourceName, $entityType, $data);
            }
        }
    }
}
