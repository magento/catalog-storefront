<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetRequestInterface;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\DataExporter\Model\FeedInterface;


class CategoryTest extends TestCase
{
    private const CATEGORY_ID = '333';
    private const STORE_CODE = 'default';

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
     * @var FeedInterface
     */
    private $categoryFeed;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->categoriesConsumer = Bootstrap::getObjectManager()->create(CategoriesConsumer::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->categoriesGetRequestInterface = Bootstrap::getObjectManager()->create(CategoriesGetRequestInterface::class);
        $this->messageBuilder = Bootstrap::getObjectManager()->create(ChangedEntitiesMessageBuilder::class);
        $this->categoryRepository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->categoryFeed = Bootstrap::getObjectManager()->get(FeedPool::class)->getFeed('categories');
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
    }

    /**
     * Validate deleted category is removed from storefront storage.
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testSaveAndDeleteCategory()
    {
        try {
            $category = $this->categoryRepository->get(self::CATEGORY_ID);
            $this->assertEquals(self::CATEGORY_ID, $category->getId());

            $message = $this->messageBuilder->build([self::CATEGORY_ID], CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE, self::STORE_CODE);
            $this->categoriesConsumer->processMessage($message);

            $this->categoriesGetRequestInterface->setIds([self::CATEGORY_ID]);
            $this->categoriesGetRequestInterface->setStore(self::STORE_CODE);
            $catalogServiceItem = $this->catalogService->getCategories($this->categoriesGetRequestInterface);
            $items = $catalogServiceItem->getItems();
            $this->assertCount(1, $items, 'Category could not be found in catalog storefront storage');
            $this->assertEquals($items[0]->getId(), self::CATEGORY_ID, 'Category could not be found in catalog storefront storage');

            $this->deleteCategory(self::CATEGORY_ID);
            $deletedFeed = $this->categoryFeed->getDeletedByIds([self::CATEGORY_ID], [self::STORE_CODE]);
            $this->assertCount(1, $deletedFeed);

            $deleteMessage = $this->messageBuilder->build([self::CATEGORY_ID], CategoriesConsumer::CATEGORIES_DELETED_EVENT_TYPE, self::STORE_CODE);
            $this->categoriesConsumer->processMessage($deleteMessage);

            $catalogServiceItem = $this->catalogService->getCategories($this->categoriesGetRequestInterface)->getItems();
            $this->assertEmpty($catalogServiceItem, 'Category has not been removed from catalog storefront storage');
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @param int $id
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws InputException
     */
    private function deleteCategory($id)
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->categoryRepository->deleteByIdentifier($id);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }
}
