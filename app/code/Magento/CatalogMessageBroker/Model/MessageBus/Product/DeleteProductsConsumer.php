<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Product;

use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteProductsRequestInterfaceFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete products from storage
 */
class DeleteProductsConsumer implements ConsumerEventInterface
{
    /**
     * @var DeleteProductsRequestInterfaceFactory
     */
    private $deleteProductsRequestInterfaceFactory;

    /**
     * @var CatalogServerInterface
     */
    private $catalogServer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeleteProductsRequestInterfaceFactory $deleteProductsRequestInterfaceFactory
     * @param CatalogServerInterface $catalogServer
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteProductsRequestInterfaceFactory $deleteProductsRequestInterfaceFactory,
        CatalogServerInterface $catalogServer,
        LoggerInterface $logger
    ) {
        $this->deleteProductsRequestInterfaceFactory = $deleteProductsRequestInterfaceFactory;
        $this->catalogServer = $catalogServer;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities, ?string $scope = null): void
    {
        $ids = [];

        foreach ($entities as $entity) {
            $ids[] = $entity->getEntityId();
        }

        $deleteProductRequest = $this->deleteProductsRequestInterfaceFactory->create();
        $deleteProductRequest->setProductIds($ids);
        $deleteProductRequest->setStore($scope);

        try {
            $importResult = $this->catalogServer->deleteProducts($deleteProductRequest);
            if ($importResult->getStatus() === false) {
                $this->logger->error(sprintf('Products deletion has failed: "%s"', $importResult->getMessage()));
            }
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while deleting products: "%s"', $e));
        }
    }
}
