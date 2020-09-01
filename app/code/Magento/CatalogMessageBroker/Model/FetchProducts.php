<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExport\Api\Data\EntityRequestDataInterfaceFactory;
use Magento\CatalogExport\Api\Data\EntitiesRequestInterfaceFactory;
use Magento\CatalogExport\Api\Data\EntitiesRequestInterface;
use Magento\CatalogExportApi\Api\Data\Product;
use Magento\CatalogExportApi\Api\ProductRepositoryInterface;
use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventData;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * @inheritdoc
 */
class FetchProducts implements FetchProductsInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var EntitiesRequestInterfaceFactory
     */
    private $entitiesRequestInterfaceFactory;

    /**
     * @var EntityRequestDataInterfaceFactory
     */
    private $entityRequestDataInterfaceFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param EntitiesRequestInterfaceFactory $entitiesRequestInterfaceFactory
     * @param EntityRequestDataInterfaceFactory $entityRequestDataInterfaceFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        DataObjectProcessor $dataObjectProcessor,
        EntitiesRequestInterfaceFactory $entitiesRequestInterfaceFactory,
        EntityRequestDataInterfaceFactory $entityRequestDataInterfaceFactory
    ) {
        $this->productRepository = $productRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->entitiesRequestInterfaceFactory = $entitiesRequestInterfaceFactory;
        $this->entityRequestDataInterfaceFactory = $entityRequestDataInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(EventData $eventData): array
    {
        $products = $this->productRepository->get($this->buildProductRepositoryRequest($eventData));
        $data = [];

        foreach ($products as $product) {
            $data[] = $this->dataObjectProcessor->buildOutputDataArray($product, Product::class);
        }

        return $data;
    }

    /**
     * Build product repository request
     * TODO eliminate builder when moving to REST API request
     *
     * @param EventData $eventData
     *
     * @return EntitiesRequestInterface
     */
    private function buildProductRepositoryRequest(EventData $eventData): EntitiesRequestInterface
    {
        $entitiesRequestData = [];

        foreach ($eventData->getEntities() as $entity) {
            $entityRequestData = $this->entityRequestDataInterfaceFactory->create();
            $entityRequestData->setEntityId($entity->getEntityId());
            $entityRequestData->setAttributeCodes($entity->getAttributes());

            $entitiesRequestData[] = $entityRequestData;
        }

        $entityRequest = $this->entitiesRequestInterfaceFactory->create();
        $entityRequest->setEntitiesRequestData($entitiesRequestData);
        $entityRequest->setStoreViewCodes([$eventData->getScope()]);

        return $entityRequest;
    }
}
