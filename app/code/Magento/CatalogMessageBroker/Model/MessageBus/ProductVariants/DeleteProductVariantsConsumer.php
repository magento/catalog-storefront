<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\MessageBus\ProductVariants;

use Magento\CatalogStorefrontApi\Api\VariantServiceServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteVariantsRequestInterfaceFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete product variants from storage
 */
class DeleteProductVariantsConsumer implements ConsumerEventInterface
{
    /**
     * @var DeleteVariantsRequestInterfaceFactory
     */
    private $deleteVariantsRequestInterfaceFactory;

    /**
     * @var VariantServiceServerInterface
     */
    private $variantsServer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeleteVariantsRequestInterfaceFactory $deleteVariantsRequestInterfaceFactory
     * @param VariantServiceServerInterface $variantsServer
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteVariantsRequestInterfaceFactory $deleteVariantsRequestInterfaceFactory,
        VariantServiceServerInterface $variantsServer,
        LoggerInterface $logger
    ) {
        $this->deleteVariantsRequestInterfaceFactory = $deleteVariantsRequestInterfaceFactory;
        $this->variantsServer = $variantsServer;
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

        $deleteVariantsRequest = $this->deleteVariantsRequestInterfaceFactory->create();
        $deleteVariantsRequest->setId($ids);

        try {
            $importResult = $this->variantsServer->deleteProductVariants($deleteVariantsRequest);
            if ($importResult->getStatus() === false) {
                $this->logger->error(
                    sprintf('Product variants deletion has failed: "%s"', $importResult->getMessage())
                );
            }
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while deleting product variants: "%s"', $e));
        }
    }
}
