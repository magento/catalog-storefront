<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\MessageBus;

use Magento\CatalogStorefront\Model\Storage\Client\CommandInterface;
use Magento\CatalogStorefront\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Psr\Log\LoggerInterface;

/**
 * Consumer for store data to data storage.
 */
class Consumer
{
    private const DELETE = 'delete';
    private const SAVE = 'save';

    /**
     * @var CommandInterface
     */
    private $storageWriteSource;

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
     * @var DataDefinitionInterface
     */
    private $storageSchemaManager;

    /**
     * @param CommandInterface $storageWriteSource
     * @param DataDefinitionInterface $storageSchemaManager
     * @param State $storageState
     * @param CatalogItemMessageBuilder $catalogItemMessageBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandInterface $storageWriteSource,
        DataDefinitionInterface $storageSchemaManager,
        State $storageState,
        CatalogItemMessageBuilder $catalogItemMessageBuilder,
        LoggerInterface $logger
    ) {
        $this->storageWriteSource = $storageWriteSource;
        $this->storageSchemaManager = $storageSchemaManager;
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
        foreach ($messages as $message) {
            $entity = $this->catalogItemMessageBuilder->build($message);
            $eventType = $entity->getEventType();

            if (false !== strpos($eventType, 'deleted')) {
                $dataPerType[$entity->getEntityType()][$entity->getStoreId()][self::DELETE][] = $entity->getEntityId();
                continue;
            }

            $entityData = $entity->getEntityData();
            $entityData['id'] = $entity->getEntityId();
            $entityData['store_id'] = $entity->getStoreId();
            $dataPerType[$entity->getEntityType()][$entity->getStoreId()][self::SAVE][] = $entityData;
        }

        return $dataPerType;
    }

    /**
     * Save catalog data to the internal storage
     *
     * @param array $dataPerType
     * @throws \Magento\Framework\Exception\BulkException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function saveToStorage(array $dataPerType): void
    {
        foreach ($dataPerType as $entityType => $dataPerStore) {
            foreach ($dataPerStore as $storeId => $data) {
                $sourceName = $this->storageState->getCurrentDataSourceName([$storeId, $entityType]);
                $this->deleteEntities($data[self::DELETE] ?? [], $sourceName, $entityType);
                $this->saveEntities($data[self::SAVE] ?? [], $sourceName, $entityType);
            }
        }
    }

    /**
     * @param array $data
     * @param string $sourceName
     * @param string $entityType
     * @return void
     */
    private function deleteEntities(array $data, string $sourceName, string $entityType): void
    {
        if (!$data) {
            return;
        }
        if (!$this->storageSchemaManager->existsDataSource($sourceName)) {
            $this->logger->debug(
                \sprintf('Cannot delete entities "%s": Index "%s" does not exist', \implode(',', $data), $sourceName)
            );
            return;
        }

        $this->logger->debug(
            \sprintf('Delete from storage "%s" %s record(s)', $sourceName, count($data)),
            ['verbose' => $data]
        );
        $this->storageWriteSource->bulkDelete($sourceName, $entityType, $data);
    }

    /**
     * @param array $data
     * @param string $sourceName
     * @param string $entityType
     * @throws \Magento\Framework\Exception\BulkException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    private function saveEntities(array $data, string $sourceName, string $entityType): void
    {
        if (!$data) {
            return;
        }
        $this->logger->debug(
            \sprintf('Save to storage "%s" %s record(s)', $sourceName, count($data)),
            ['verbose' => $data]
        );

        // TODO: MC-31204
        // TODO: MC-31155
        if (!$this->storageSchemaManager->existsDataSource($sourceName)) {
            $this->storageSchemaManager->createDataSource($sourceName, []);
            $this->storageSchemaManager->createEntity($sourceName, $entityType, []);
        }

        //TODO batching
        $this->storageWriteSource->bulkInsert($sourceName, $entityType, $data);
    }
}
