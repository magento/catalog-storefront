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
use Magento\CatalogMessageBroker\HttpClient\RestClient;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class FetchCategories implements FetchCategoriesInterface
{
    /**
     * Route to Export API categories retrieval
     */
    private const EXPORT_API_GET_CATEGORIES = '/V1/catalog-export/categories';

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var EntitiesRequestInterfaceFactory
     */
    private $entitiesRequestInterfaceFactory;

    /**
     * @var EntityRequestDataInterfaceFactory
     */
    private $entityRequestDataInterfaceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param RestClient $restClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        RestClient $restClient,
        LoggerInterface $logger
    ) {
        $this->restClient = $restClient;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(EventData $eventData): array
    {
        try {
            $categories = $this->restClient->get(
                self::EXPORT_API_GET_CATEGORIES,
                ['ids' => $ids, 'storeViewCodes' => $storeViewCodes],
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Cannot load categories via "%s" with ids "%s"',
                    self::EXPORT_API_GET_CATEGORIES,
                    \implode(',', $ids)
                ),
                ['exception' => $e]
            );
            return [];
        }

        return $categories;
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
