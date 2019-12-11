<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\MessageBus;

use Magento\CatalogProduct\Model\Storage\ClientInterface;
use Magento\CatalogProduct\Model\Storage\State;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Consumer for store data to data storage.
 */
class Consumer
{
    /**
     * @var ClientInterface
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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ClientInterface $storage
     * @param State $storageState
     * @param SerializerInterface $serializer
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $storage,
        State $storageState,
        SerializerInterface $serializer,
        EntityManager $entityManager,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->storageState = $storageState;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Process
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation
     * @throws \Exception
     *
     * @return void
     */
    public function process(\Magento\AsynchronousOperations\Api\Data\OperationInterface $operation)
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            $this->storage->bulkInsert($this->storageState->getAliasName(), 'product', $data);
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __('Sorry, something went wrong during population data storage. Please see log for details.');
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }
}
