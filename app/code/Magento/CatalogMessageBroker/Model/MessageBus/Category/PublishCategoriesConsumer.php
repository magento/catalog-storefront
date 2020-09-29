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
use Magento\CatalogStorefrontApi\Api\Data\CategoryMapper;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterfaceFactory;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportRequestAttributesMapper;
use Psr\Log\LoggerInterface;

/**
 * Publish categories into storage
 */
class PublishCategoriesConsumer implements ConsumerEventInterface
{
    /**
     * Action type update
     */
    public const ACTION_UPDATE = 'categories_update';

    /**
     * Action type import
     */
    public const ACTION_IMPORT = 'categories_import';

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
     * @var CategoryMapper
     */
    private $categoryMapper;

    /**
     * @var AttributeCodesConverter
     */
    private $attributeCodesConverter;

    /**
     * @var ImportRequestAttributesMapper
     */
    private $importRequestAttributesMapper;

    /**
     * @param LoggerInterface $logger
     * @param FetchCategoriesInterface $fetchCategories
     * @param CatalogServerInterface $catalogServer
     * @param ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory
     * @param CategoryMapper $categoryMapper
     * @param AttributeCodesConverter $attributeCodesConverter
     * @param ImportRequestAttributesMapper $importRequestAttributesMapper
     */
    public function __construct(
        LoggerInterface $logger,
        FetchCategoriesInterface $fetchCategories,
        CatalogServerInterface $catalogServer,
        ImportCategoriesRequestInterfaceFactory $importCategoriesRequestInterfaceFactory,
        CategoryMapper $categoryMapper,
        AttributeCodesConverter $attributeCodesConverter,
        ImportRequestAttributesMapper $importRequestAttributesMapper
    ) {
        $this->logger = $logger;
        $this->fetchCategories = $fetchCategories;
        $this->catalogServer = $catalogServer;
        $this->importCategoriesRequestInterfaceFactory = $importCategoriesRequestInterfaceFactory;
        $this->categoryMapper = $categoryMapper;
        $this->attributeCodesConverter = $attributeCodesConverter;
        $this->importRequestAttributesMapper = $importRequestAttributesMapper;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities, string $scope): void
    {
        $categoriesData = $this->fetchCategories->execute($entities, $scope);
        $attributesArray = $this->getAttributesArray($entities);
        $importCategories = [];
        $updateCategories = [];

        foreach ($categoriesData as $categoryData) {
            $attributes = $attributesArray[$categoryData['category_id']];

            if (!empty($attributes)) {
                $updateCategories[$categoryData['category_id']] = $this->filterAttributes($categoryData, $attributes);
            } else {
                $importCategories[$categoryData['category_id']] = $categoryData;
            }
        }

        if (!empty($importCategories)) {
            $this->importCategories($importCategories, $scope, self::ACTION_IMPORT);
        }

        if (!empty($updateCategories)) {
            $this->importCategories($updateCategories, $scope, self::ACTION_UPDATE);
        }
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
     * @param array $attributes
     *
     * @return array
     */
    private function filterAttributes(array $categoryData, array $attributes): array
    {
        return \array_filter(
            $categoryData,
            function ($code) use ($attributes) {
                $attributes = $this->attributeCodesConverter->convertFromCamelCaseToSnakeCase($attributes);

                return \in_array($code, $attributes) || $code === 'category_id';
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Import categories
     *
     * @param array $categories
     * @param string $storeCode
     * @param string $actionType
     *
     * @return void
     *
     * @throws \Throwable
     */
    private function importCategories(array $categories, string $storeCode, string $actionType): void
    {
        $attributes = [];

        foreach ($categories as &$category) {
            // be sure, that data passed to Import API in the expected format
            $category['id'] = $category['category_id'];

            if ($actionType === self::ACTION_UPDATE) {
                $attributes[] = $this->importRequestAttributesMapper->setData(
                    [
                        'entity_id' => $category['category_id'],
                        'attribute_codes' => \array_keys($category),
                    ]
                )->build();
            }

            $category = $this->categoryMapper->setData($category)->build();
        }

        $importCategoriesRequest = $this->importCategoriesRequestInterfaceFactory->create();
        $importCategoriesRequest->setCategories($categories);
        $importCategoriesRequest->setStore($storeCode);
        $importCategoriesRequest->setAttributes($attributes);

        $importResult = $this->catalogServer->importCategories($importCategoriesRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Categories import is failed: "%s"', $importResult->getMessage()));
        }
    }
}
