<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\Category\CategoriesConsumer;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryArrayMapper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\RuntimeException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for Categories message bus
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TestCategories extends StorefrontTestsAbstract
{
    private const STORE_CODE = 'default';

    private $attributeCodes = [
        'id',
        'path',
        'position',
        'level',
        'children_count',
        'name',
        'display_mode',
        'default_sort_by',
        'url_key',
        'url_path',
        'is_active',
        'is_anchor',
        'include_in_menu',
        'available_sort_by',
        'breadcrumbs',
        'description',
        'canonical_url',
        'product_count',
        'children',
        'image',
        'parent_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'attributes'
    ];

    /**
     * @var CategoriesConsumer
     */
    private $categoriesConsumer;

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var CategoriesGetRequestInterface
     */
    private $categoriesGetRequestInterface;

    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryArrayMapper
     */
    private $arrayMapper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->categoriesConsumer = Bootstrap::getObjectManager()->create(CategoriesConsumer::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->categoriesGetRequestInterface = Bootstrap::getObjectManager()->create(
            CategoriesGetRequestInterface::class
        );
        $this->messageBuilder = Bootstrap::getObjectManager()->create(ChangedEntitiesMessageBuilder::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->get(CategoryArrayMapper::class);
    }

    /**
     * Validate category data retrieved from SF API after whole cycle save/index/export/import
     *
     * @magentoDataFixture Magento/Catalog/_files/category_specific_fields.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws \Throwable
     * @dataProvider categoryDataProvider
     */
    public function testCategoryData(array $expected): void
    {
        $this->runCategoryConsumer(10);
        $actual = $this->getApiResults(10, $this->attributeCodes);
        self::assertEquals($expected, $actual);
    }

    /**
     * Validate category breadcrumbs data retrieved from SF API after whole cycle save/index/export/import
     *
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDbIsolation disabled
     * @param array $expected
     * @throws \Throwable
     * @dataProvider categoryBreadcrumbsProvider
     */
    public function testCategoryBreadcrumbsData(array $expected): void
    {
        $this->runCategoryConsumer(402);
        $actual = $this->getApiResults(402, ['breadcrumbs']);
        self::assertArrayHasKey('breadcrumbs', $actual);
        self::assertEquals($expected, $actual['breadcrumbs']);
    }

    public function categoryDataProvider(): array
    {
        return [
            [
                [
                    'id' => '10',
                    'path' => '1/2/3',
                    'position' => 1,
                    'level' => 2,
                    'children_count' => 0,
                    'name' => 'Category_en',
                    'display_mode' => 'PRODUCTS_AND_PAGE',
                    'default_sort_by' => 'price',
                    'url_key' => 'category-en',
                    'url_path' => 'category-en',
                    'is_active' => true,
                    'is_anchor' => true,
                    'include_in_menu' => false,
                    'available_sort_by' => [
                        0 => 'name',
                        1 => 'price',
                    ],
                    'breadcrumbs' => [],
                    'description' => 'Category_en Description',
                    'canonical_url' => '',
                    'product_count' => 0,
                    'children' => [],
                    'image' => '',
                    'parent_id' => '2',
                    'meta_title' => 'Category_en Meta Title',
                    'meta_description' => 'Category_en Meta Description',
                    'meta_keywords' => 'Category_en Meta Keywords',
                    'attributes' => []
                ]
            ]
        ];
    }

    public function categoryBreadcrumbsProvider(): array
    {
        return [
            [
                [
                    [
                        "category_id" => "400",
                        "category_name" => "Category 1",
                        "category_level" => 2,
                        "category_url_key" => "category-1",
                        "category_url_path" => "category-1"
                    ],
                    [
                        "category_id" => "401",
                        "category_name" => "Category 1.1",
                        "category_level" => 3,
                        "category_url_key" => "category-1-1",
                        "category_url_path" => "category-1/category-1-1"
                    ]
                ]
            ]
        ];
    }

    /**
     * @param int $categoryId
     * @throws NoSuchEntityException
     */
    private function runCategoryConsumer($categoryId): void
    {
        $category = $this->categoryRepository->get($categoryId);
        self::assertEquals($categoryId, $category->getId());
        $entitiesData = [
            [
                'entity_id' => (int)$category->getId(),
            ]
        ];
        $message = $this->messageBuilder->build(
            CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE,
            $entitiesData,
            self::STORE_CODE
        );
        $this->categoriesConsumer->processMessage($message);
    }

    /**
     * @param int $categoryId
     * @param array $attributes
     * @return array
     * @throws FileSystemException
     * @throws RuntimeException
     * @throws \Throwable
     */
    private function getApiResults($categoryId, $attributes): array
    {
        $this->categoriesGetRequestInterface->setIds([$categoryId]);
        $this->categoriesGetRequestInterface->setStore(self::STORE_CODE);
        $this->categoriesGetRequestInterface->setAttributeCodes($attributes);
        $catalogServiceItem = $this->catalogService->getCategories($this->categoriesGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());
        $item = $catalogServiceItem->getItems()[0];
        self::assertEquals($item->getId(), $categoryId);

        return $this->arrayMapper->convertToArray($item);
    }
}
