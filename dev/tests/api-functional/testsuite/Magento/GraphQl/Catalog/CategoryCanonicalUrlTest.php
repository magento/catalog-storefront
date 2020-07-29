<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\CatalogDataExporter\Model\Indexer\CategoryFeedIndexer;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\TestFramework\Workaround\ConsumerInvoker;
use Magento\Indexer\Model\Indexer;

/**
 * Test for getting canonical url data from category
 */
class CategoryCanonicalUrlTest extends GraphQlAbstract
{
    /** @var ObjectManager $objectManager */
    private $objectManager;

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @magentoConfigFixture default_store catalog/seo/category_canonical_tag 1
     */
    public function testCategoryWithCanonicalLinksMetaTagSettingsEnabled()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->create(CategoryCollection::class);
        $categoryCollection->addFieldToFilter('name', 'Category 1.1.1');
        /** @var CategoryInterface $category */
        $category = $categoryCollection->getFirstItem();

        $this->reindexCategoryExport();
        $this->invokeConsumers(['catalog.category.export.consumer', 'catalog.product.export.consumer']);

        $categoryId = $category->getId();
        $query = <<<QUERY
    {
categoryList(filters: {ids: {in: ["$categoryId"]}}) {
    id
    name
   url_key
   url_suffix
   canonical_url
 }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['categoryList'], 'Category list should not be empty');
        $this->assertEquals('.html', $response['categoryList'][0]['url_suffix']);
        $this->assertEquals(
            'category-1/category-1-1/category-1-1-1.html',
            $response['categoryList'][0]['canonical_url']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/categories.php
     * @magentoConfigFixture default_store catalog/seo/category_canonical_tag 0
     */
    public function testCategoryWithCanonicalLinksMetaTagSettingsDisabled()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->objectManager->create(CategoryCollection::class);
        $categoryCollection->addFieldToFilter('name', 'Category 1.1');
        /** @var CategoryInterface $category */
        $category = $categoryCollection->getFirstItem();

        $this->reindexCategoryExport();
        $this->invokeConsumers(['catalog.category.export.consumer', 'catalog.product.export.consumer']);

        $categoryId = $category->getId();
        $query = <<<QUERY
    {
categoryList(filters: {ids: {in: ["$categoryId"]}}) {
    id
    name
   url_key
   canonical_url
 }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['categoryList'], 'Category list should not be empty');
        $this->assertNull(
            $response['categoryList'][0]['canonical_url']
        );
        $this->assertEquals('category-1-1', $response['categoryList'][0]['url_key']);
    }

    /**
     * Clean category feed index
     * @throws \Exception
     */
    private function reindexCategoryExport()
    {
        /** @var $indexer \Magento\Framework\Indexer\IndexerInterface */
        $indexer = $this->objectManager->create(Indexer::class);
        $indexer->load(CategoryFeedIndexer::INDEXER_ID);
        $indexer->reindexAll();
    }

    /**
     * Invoke consumers
     *
     * @param array $consumersToProcess
     * @return void
     * @throws LocalizedException
     * @throws \ReflectionException
     */
    private function invokeConsumers(array $consumersToProcess = []): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(false, $consumersToProcess);
    }
}
