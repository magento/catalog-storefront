<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Category;

use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesRequestInterfaceFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete categories from storage
 */
class DeleteCategoriesConsumer implements ConsumerEventInterface
{
    /**
     * @var DeleteCategoriesRequestInterfaceFactory
     */
    private $deleteCategoriesRequestInterfaceFactory;

    /**
     * @var CatalogServerInterface
     */
    private $catalogServer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeleteCategoriesRequestInterfaceFactory $deleteCategoriesRequestInterfaceFactory
     * @param CatalogServerInterface $catalogServer
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteCategoriesRequestInterfaceFactory $deleteCategoriesRequestInterfaceFactory,
        CatalogServerInterface $catalogServer,
        LoggerInterface $logger
    ) {
        $this->deleteCategoriesRequestInterfaceFactory = $deleteCategoriesRequestInterfaceFactory;
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

        $deleteCategoryRequest = $this->deleteCategoriesRequestInterfaceFactory->create();
        $deleteCategoryRequest->setCategoryIds($ids);
        $deleteCategoryRequest->setStore($scope);
        $importResult = $this->catalogServer->deleteCategories($deleteCategoryRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Categories deletion has failed: "%s"', $importResult->getMessage()));
        }
    }
}
