<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Test\Integration\CatalogService;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogDataExporter\Test\Integration\Category\AbstractCategoryTest;
use Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetRequest;
use Magento\Framework\Exception\InputException as InputExceptionAlias;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

class CategoriesTest extends AbstractCategoryTest
{
    const TEST_ID = '333';
    const STORE_CODE = 'default';
    const ERROR_MESSAGE = 'Cannot find categories for ids "%s" in the scope "%s"';

    /**
     * @var CategoriesConsumer
     */
    private $categoryConsumer;

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var CategoriesGetRequest
     */
    private $categoryGetRequestInterface;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryConsumer = Bootstrap::getObjectManager()->create(CategoriesConsumer::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->categoryGetRequestInterface = Bootstrap::getObjectManager()->create(CategoriesGetRequest::class);
        $this->categoryRepositoryInterface = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
    }

    /**
     * Validate deleted categories are removed from StoreFront
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @throws StateException
     * @throws \Throwable
     * @throws \Zend_Db_Statement_Exception
     */
    public function testDeleteCategory()
    {
        $category = $this->getCategory(self::TEST_ID);
        $this->assertEquals(self::TEST_ID, $category->getId());
        $this->categoryConsumer->processMessage("[\"" . $category->getId() . "\"]");

        $this->categoryGetRequestInterface->setIds([$category->getId()]);
        $this->categoryGetRequestInterface->setStore("default");
        $catalogServiceItem = $this->catalogService->getCategories($this->categoryGetRequestInterface);
        $item = $catalogServiceItem->getItems()[0];
        $this->assertEquals($item->getId(), $category->getId());
        $this->deleteCategory($category->getId());

        $extractedCategory = $this->getExtractedCategory(self::TEST_ID, self::STORE_CODE);
        $this->assertEquals(1, (int)$extractedCategory['is_deleted']);
        $this->categoryConsumer->processMessage("[\"" . $category->getId() . "\"]");
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(self::ERROR_MESSAGE, self::TEST_ID, self::STORE_CODE));
        $this->catalogService->getCategories($this->categoryGetRequestInterface);
    }

    /**
     * Get the category by ID
     *
     * @param $id
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    private function getCategory($id)
    {
        try {
            return $this->categoryRepositoryInterface->get($id);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException();
        }
    }

    /**
     * Delete the category by ID
     *
     * @param $id
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws InputExceptionAlias
     */
    private function deleteCategory($id)
    {
        try {
            $registry = Bootstrap::getObjectManager()->get(Registry::class);
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', true);
            $this->categoryRepositoryInterface->deleteByIdentifier($id);
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', false);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException();
        }
    }
}
