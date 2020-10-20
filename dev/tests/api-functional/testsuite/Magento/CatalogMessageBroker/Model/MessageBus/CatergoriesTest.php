<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Magento\CatalogMessageBroker\Model\MessageBus\Category\CategoriesConsumer;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetRequestInterface;
use Magento\DataExporter\Model\FeedInterface;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for Categories message bus
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatergoriesTest extends StorefrontTestsAbstract
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
        $this->categoriesGetRequestInterface = Bootstrap::getObjectManager()->create(
            CategoriesGetRequestInterface::class
        );
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
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws FileSystemException
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function testSaveAndDeleteCategory() : void
    {
        $category = $this->categoryRepository->get(self::CATEGORY_ID);
        self::assertEquals(self::CATEGORY_ID, $category->getId());
        $entitiesData = [
            [
                'entity_id' => (int) $category->getId(),
            ]
        ];
        $message = $this->messageBuilder->build(
            CategoriesConsumer::CATEGORIES_UPDATED_EVENT_TYPE,
            $entitiesData,
            self::STORE_CODE
        );
        $this->categoriesConsumer->processMessage($message);

        $this->categoriesGetRequestInterface->setIds([$category->getId()]);
        $this->categoriesGetRequestInterface->setStore(self::STORE_CODE);
        $catalogServiceItem = $this->catalogService->getCategories($this->categoriesGetRequestInterface);
        self::assertNotEmpty($catalogServiceItem->getItems());
        $item = $catalogServiceItem->getItems()[0];
        self::assertEquals($item->getId(), $category->getId());

        $this->deleteCategory((int)$category->getId());
        $deletedFeed = $this->categoryFeed->getDeletedByIds([$category->getId()], [self::STORE_CODE]);
        self::assertNotEmpty($deletedFeed);

        $deleteMessage = $this->messageBuilder->build(
            CategoriesConsumer::CATEGORIES_DELETED_EVENT_TYPE,
            $entitiesData,
            self::STORE_CODE
        );
        $this->categoriesConsumer->processMessage($deleteMessage);

        $items = $this->catalogService->getCategories($this->categoriesGetRequestInterface)->getItems();
        self::assertEmpty($items);
    }

    /**
     * @param int $id
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    private function deleteCategory(int $id) : void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->categoryRepository->deleteByIdentifier($id);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }
}
