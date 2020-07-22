<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogMessageBroker\Model\FetchVariantInterface;
use Magento\CatalogMessageBroker\Model\SerializerInterface;
use Magento\CatalogStorefront\Model\Storage\Client\CommandInterface;
use Magento\CatalogStorefront\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\CatalogStorefront\Model\MessageBus\Consumer as OldConsumer;
use Magento\CatalogStorefront\Model\MessageBus\CatalogItemMessageBuilder;
use Psr\Log\LoggerInterface;

/**
 * Sync Product Variants data to Catalog Storefront Storage by list of Product Variants ids.
 */
class VariantConsumer extends OldConsumer
{
    /**
     * @var FetchVariantInterface
     */
    private $fetchVariant;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param CommandInterface $storageWriteSource
     * @param DataDefinitionInterface $storageSchemaManager
     * @param State $storageState
     * @param CatalogItemMessageBuilder $catalogItemMessageBuilder
     * @param LoggerInterface $logger
     * @param FetchVariantInterface $fetchVariant
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CommandInterface $storageWriteSource,
        DataDefinitionInterface $storageSchemaManager,
        State $storageState,
        CatalogItemMessageBuilder $catalogItemMessageBuilder,
        LoggerInterface $logger,
        FetchVariantInterface $fetchVariant,
        SerializerInterface $serializer
    ) {
        parent::__construct(
            $storageWriteSource,
            $storageSchemaManager,
            $storageState,
            $catalogItemMessageBuilder,
            $logger
        );
        $this->logger = $logger;
        $this->fetchVariant = $fetchVariant;
        $this->serializer = $serializer;
    }

    /**
     * Process message.
     *
     * @param string $ids
     */
    public function processMessage(string $ids)
    {
        try {
            $ids = $this->deserializeIds($ids);
            $data = $this->fetchVariantData($ids);
            $this->saveToStorage($data);
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Fetch Product Variants data by ids.
     *
     * @param int[] $ids
     * @return array
     */
    private function fetchVariantData(array $ids): array
    {
        $dataPerType = [];
        foreach ($this->fetchVariant->execute($ids) as $variant) {
            $dataPerType['variant'][$variant['store_id']][self::SAVE][] = $variant;
        }
        return $dataPerType;
    }

    /**
     * Deserialize data.
     *
     * @param string $ids
     * @return int[]
     */
    private function deserializeIds(string $ids): array
    {
        return json_decode($ids, true);
    }
}
