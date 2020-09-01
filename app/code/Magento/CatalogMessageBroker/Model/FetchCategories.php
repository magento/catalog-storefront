<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExport\Api\Data\EntitiesRequestInterface;
use Magento\CatalogExport\Api\Data\EntitiesRequestInterfaceFactory;
use Magento\CatalogExport\Api\Data\EntityRequestDataInterfaceFactory;
use Magento\CatalogExportApi\Api\CategoryRepositoryInterface;
use Magento\CatalogExportApi\Api\Data\Category;
use Magento\CatalogMessageBroker\Model\MessageBus\Event\EventData;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * @inheritdoc
 */
class FetchCategories implements FetchCategoriesInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

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
     * @param CategoryRepositoryInterface $categoryRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param EntitiesRequestInterfaceFactory $entitiesRequestInterfaceFactory
     * @param EntityRequestDataInterfaceFactory $entityRequestDataInterfaceFactory
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        DataObjectProcessor $dataObjectProcessor,
        EntitiesRequestInterfaceFactory $entitiesRequestInterfaceFactory,
        EntityRequestDataInterfaceFactory $entityRequestDataInterfaceFactory
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->categoryRepository = $categoryRepository;
        $this->entitiesRequestInterfaceFactory = $entitiesRequestInterfaceFactory;
        $this->entityRequestDataInterfaceFactory = $entityRequestDataInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(EventData $eventData): array
    {
        $categories = $this->categoryRepository->get($this->buildCategoryRepositoryRequest($eventData));
        $data = [];

        foreach ($categories as $category) {
            $data[] = $this->dataObjectProcessor->buildOutputDataArray($category, Category::class);
        }

        return $data;
    }

    /**
     * Build category repository request
     * TODO eliminate builder when moving to REST API request
     *
     * @param EventData $eventData
     *
     * @return EntitiesRequestInterface
     */
    private function buildCategoryRepositoryRequest(EventData $eventData): EntitiesRequestInterface
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
