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
    const TEST_SKU = 'bundle-product';
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
     * @var ProductRepositoryInterface
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
        $collection = $this->categoryFactory->create()->getCollection()
            ->addAttributeToFilter('name',['in' => ['Category 1'] ])->setPageSize(1);

        if ($collection->getSize()) {
            $category = $collection->getFirstItem();
        }

        $this->categoriesGetRequestInterface->setIds([$category->getId()]);
        $this->categoriesGetRequestInterface->setStore(self::STORE_CODE);
        $catalogServiceItem = $this->catalogService->getCategories($this->categoriesGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        self::assertEquals($category->getId(), $catalogServiceItem->getItems()[0]->getId());
        self::assertEquals($category->getName(), $catalogServiceItem->getItems()[0]->getName());
        self::assertEquals($category->getPath(), $catalogServiceItem->getItems()[0]->getPath());
        self::assertEquals($category->getLevel(), $catalogServiceItem->getItems()[0]->getLevel());
        self::assertTrue($category->getIsActive(), $catalogServiceItem->getItems()[0]->getIsActive());
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
        $collection = $this->categoryFactory->create()->getCollection()
            ->addAttributeToFilter('name',['in' => ['Category_Anchor'] ])->setPageSize(1);

        if ($collection->getSize()) {
            $category = $collection->getFirstItem();
        }

        $this->categoriesGetRequestInterface->setIds([$category->getId()]);
        $this->categoriesGetRequestInterface->setStore(self::STORE_CODE);
        $catalogServiceItem = $this->catalogService->getCategories($this->categoriesGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        self::assertEquals($category->getId(), $catalogServiceItem->getItems()[0]->getId());
        self::assertEquals($category->getName(), $catalogServiceItem->getItems()[0]->getName());
        self::assertEquals($category->getPath(), $catalogServiceItem->getItems()[0]->getPath());
        self::assertEquals($category->getLevel(), $catalogServiceItem->getItems()[0]->getLevel());
        self::assertTrue($category->getIsActive(), $catalogServiceItem->getItems()[0]->getIsActive());
    }
}
