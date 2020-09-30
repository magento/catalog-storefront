<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Category;

use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use Magento\Catalog\Model\CategoryFactory;

/**
 * Test for categories from store front api
 */
class TestCategories extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const STORE_CODE = 'default';

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var CategoriesGetRequestInterface
     */
    private $categoriesGetRequestInterface;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CompareArraysRecursively
     */
    private $compareArraysRecursively;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->categoriesGetRequestInterface = Bootstrap::getObjectManager()->create(CategoriesGetRequestInterface::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->categoryFactory = Bootstrap::getObjectManager()->create(CategoryFactory::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Validate category data
     *
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testCategoriesTree()
    {
        $this->categoriesGetRequestInterface->setIds([400, 402]);
        $this->categoriesGetRequestInterface->setStore(self::STORE_CODE);
        $catalogServiceItem = $this->catalogService->getCategories($this->categoriesGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        self::assertEquals(400, $catalogServiceItem->getItems()[0]->getId());
        self::assertEquals('Category 1', $catalogServiceItem->getItems()[0]->getName());
        self::assertEquals(2, $catalogServiceItem->getItems()[0]->getParentId());
        self::assertEquals('1/2/400', $catalogServiceItem->getItems()[0]->getPath());
        self::assertEquals(2, $catalogServiceItem->getItems()[0]->getLevel());
        self::assertTrue(true, $catalogServiceItem->getItems()[0]->getIsActive());
        self::assertEquals('name', $catalogServiceItem->getItems()[0]->getAvailableSortBy());
        self::assertEquals('name', $catalogServiceItem->getItems()[0]->getDefaultSortBy());
        self::assertEquals(1, $catalogServiceItem->getItems()[0]->getPosition());
        self::assertEquals([], $catalogServiceItem->getItems()[0]->getChildren());
        self::assertEquals([], $catalogServiceItem->getItems()[1]->getBreadcrumbs());

        self::assertEquals(401, $catalogServiceItem->getItems()[1]->getId());
        self::assertEquals('Category 1.1.1', $catalogServiceItem->getItems()[1]->getName());
        self::assertEquals(401, $catalogServiceItem->getItems()[1]->getParentId());
        self::assertEquals('1/2/400/401/402', $catalogServiceItem->getItems()[1]->getPath());
        self::assertEquals(4, $catalogServiceItem->getItems()[1]->getLevel());
        self::assertTrue(true, $catalogServiceItem->getItems()[1]->getIsActive());
        self::assertEquals('name', $catalogServiceItem->getItems()[1]->getAvailableSortBy());
        self::assertEquals('name', $catalogServiceItem->getItems()[1]->getDefaultSortBy());
        self::assertEquals(1, $catalogServiceItem->getItems()[1]->getPosition());
        self::assertEquals([], $catalogServiceItem->getItems()[1]->getChildren());
        self::assertEquals([], $catalogServiceItem->getItems()[1]->getBreadcrumbs());
    }

    /**
     * Validate category data
     *
     * @magentoDataFixture Magento/Catalog/_files/category_anchor.php
     * @magentoDbIsolation disabled
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testCategoryAnchor()
    {
        $this->categoriesGetRequestInterface->setIds([22]);
        $this->categoriesGetRequestInterface->setStore(self::STORE_CODE);
        $catalogServiceItem = $this->catalogService->getCategories($this->categoriesGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        self::assertEquals(22, $catalogServiceItem->getItems()[0]->getId());
        self::assertEquals('Category_Anchor', $catalogServiceItem->getItems()[0]->getName());
        self::assertEquals('1/2/22', $catalogServiceItem->getItems()[0]->getPath());
        self::assertEquals('2', $catalogServiceItem->getItems()[0]->getLevel());
        self::assertEquals('name', $catalogServiceItem->getItems()[0]->getAvailableSortBy());
        self::assertEquals('name', $catalogServiceItem->getItems()[0]->getDefaultSortBy());
        self::assertTrue(true, $catalogServiceItem->getItems()[0]->getIsActive());
    }
}
