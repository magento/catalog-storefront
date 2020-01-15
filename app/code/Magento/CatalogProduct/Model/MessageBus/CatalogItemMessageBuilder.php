<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\MessageBus;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\CatalogProduct\Model\Storage\Client\Config\Product;

/**
 * Catalog item message builder
 */
class CatalogItemMessageBuilder
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CatalogItemMessageFactory
     */
    private $catalogItemFactory;

    /**
     * @param CatalogItemMessageFactory $catalogItemFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CatalogItemMessageFactory $catalogItemFactory,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->catalogItemFactory = $catalogItemFactory;
    }

    /**
     * Build catalog item
     *
     * @param array $message
     * @return CatalogItemMessage
     */
    public function build($message): CatalogItemMessage
    {
        $message = $this->serializer->unserialize($message);
        $this->validateMessage($message);

        /** @var CatalogItemMessage $catalogItem */
        $catalogItem = $this->catalogItemFactory->create(
            [
                'entityType' => $message['entity_type'],
                'entityId' => (int)$message['entity_id'],
                'storeId' => (int)$message['store_id'],
                'entityData' => $message['entity_data']
            ]
        );

        return $catalogItem;
    }

    /**
     * Check entity type before put data to storage
     *
     * @param array $message
     * @throws \LogicException
     */
    private function validateMessage($message): void
    {
        if (!isset($message['entity_type'], $message['entity_id'], $message['store_id'], $message['entity_data'])) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Message "%s" do not contains all necessary fields: entity_type, entity_id, store_id, entity_data',
                    $this->serializer->serialize($message)
                )
            );
        }

        if (!\in_array($message['entity_type'], [Product::ENTITY_NAME], true)) {
            throw new \LogicException(\sprintf('Entity type "%s" is not supported', $message['entity_type']));
        }
    }
}
