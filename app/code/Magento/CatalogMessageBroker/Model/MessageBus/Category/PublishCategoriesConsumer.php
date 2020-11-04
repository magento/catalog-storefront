<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus\Category;

use Magento\CatalogExport\Event\Data\Entity;
use Magento\CatalogMessageBroker\Model\Converter\AttributeCodesConverter;
use Magento\CatalogMessageBroker\Model\FetchCategoriesInterface;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterfaceFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoryDataRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoryDataRequestMapper;
use Psr\Log\LoggerInterface;

/**
 * Publish categories into storage
 */
class PublishCategoriesConsumer implements ConsumerEventInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FetchCategoriesInterface
     */
    private $fetchCategories;

    /**
     * @var CatalogServerInterface
     */
    private $catalogServer;

    /**
     * @var ImportCategoriesRequestInterfaceFactory
     */
    private $importCategoriesRequestInterfaceFactory;

    /**
     * @var AttributeCodesConverter
     */
    private $attributeCodesConverter;

    /**
     * @var ImportCategoryDataRequestMapper
     */
    private $importCategoryDataRequestMapper;

    /**
     * @param LoggerInterface $logger
     * @param FetchCategoriesInterface $fetchCategories
     * @param CatalogServerInterface $catalogServer
     * @param ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory
     * @param AttributeCodesConverter $attributeCodesConverter
     * @param ImportCategoryDataRequestMapper $importCategoryDataRequestMapper
     */
    public function __construct(
        LoggerInterface $logger,
        FetchCategoriesInterface $fetchCategories,
        CatalogServerInterface $catalogServer,
        ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory,
        AttributeCodesConverter $attributeCodesConverter,
        ImportCategoryDataRequestMapper $importCategoryDataRequestMapper
    ) {
        $this->logger = $logger;
        $this->fetchCategories = $fetchCategories;
        $this->catalogServer = $catalogServer;
        $this->importCategoriesRequestInterfaceFactory = $importCategoriesRequestInterfaceFactory;
        $this->attributeCodesConverter = $attributeCodesConverter;
        $this->importCategoryDataRequestMapper = $importCategoryDataRequestMapper;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities, string $scope): void
    {
        $categoriesData = $this->fetchCategories->execute($entities, $scope);
        $attributesArray = $this->getAttributesArray($entities);
        $categories = [];

        foreach ($categoriesData as $categoryData) {
            $attributes = $attributesArray[$categoryData['category_id']];
            $categoryData['id'] = $categoryData['category_id'];
            $categoryData = $this->sortBreadcrumbsData($categoryData);

            if (!empty($attributes)) {
                $categoryData = $this->filterAttributes($categoryData, $attributes);
                $attributes = \array_keys($categoryData);
            }

            $categories[] = $this->buildCategoryDataRequest($categoryData, $attributes);
        }

        if (!empty($categories)) {
            $this->importCategories($categories, $scope);
        }
    }

    /**
     * Sort breadcrumbs data by category level in ascending order
     *
     * @param array $categoryData
     *
     * @return array
     */
    private function sortBreadcrumbsData(array $categoryData): array
    {
        if (!empty($categoryData['breadcrumbs'])) {
            \usort($categoryData['breadcrumbs'], function ($a, $b) {
                return $a['category_level'] > $b['category_level'];
            });
        }

        return $categoryData;
    }

    /**
     * Retrieve transformed entities attributes data (entity_id => attributes)
     *
     * @param Entity[] $entities
     *
     * @return array
     */
    private function getAttributesArray(array $entities): array
    {
        $attributesArray = [];
        foreach ($entities as $entity) {
            $attributesArray[$entity->getEntityId()] = $entity->getAttributes();
        }

        return $attributesArray;
    }

    /**
     * Filter attributes for entity update.
     *
     * @param array $categoryData
     * @param string[] $attributes
     *
     * @return array
     */
    private function filterAttributes(array $categoryData, array $attributes): array
    {
        return \array_filter(
            $categoryData,
            function ($code) use ($attributes) {
                $attributes = $this->attributeCodesConverter->convertFromCamelCaseToSnakeCase($attributes);

                return \in_array($code, $attributes) || $code === 'id';
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Build category data request
     *
     * @param array $category
     * @param array $attributes
     *
     * @return ImportCategoryDataRequestInterface
     */
    private function buildCategoryDataRequest(array $category, array $attributes): ImportCategoryDataRequestInterface
    {
        return $this->importCategoryDataRequestMapper->setData(
            [
                'category' => $category,
                'attributes' => $attributes,
            ]
        )->build();
    }

    /**
     * Import categories
     *
     * @param ImportCategoryDataRequestInterface[] $categoriesRequestData
     * @param string $storeCode
     *
     * @return void
     *
     * @throws \Throwable
     */
    private function importCategories(array $categoriesRequestData, string $storeCode): void
    {
        $importCategoriesRequest = $this->importCategoriesRequestInterfaceFactory->create();
        $importCategoriesRequest->setCategories($categoriesRequestData);
        $importCategoriesRequest->setStore($storeCode);

        $importResult = $this->catalogServer->importCategories($importCategoriesRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Categories import is failed: "%s"', $importResult->getMessage()));
        }
    }
}
