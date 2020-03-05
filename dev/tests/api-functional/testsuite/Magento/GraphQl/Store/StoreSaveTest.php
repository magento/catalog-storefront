<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\CatalogSearch\Model\Indexer\Fulltext as IndexerSearch;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class StoreSaveTest
 */
class StoreSaveTest extends GraphQlAbstract
{
    /**
     * Consumers list.
     */
    private const CONSUMERS = [
        'storefront.catalog.product.update',
        'storefront.catalog.category.update',
        'storefront.catalog.data.consume',
    ];

    /**
     * Test a product from a specific and a default store
     *
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoApiDataFixture Magento/Catalog/_files/category_product.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductVisibleInNewStore()
    {
        $storeCodeFromFixture = 'test';
        $this->testProduct($storeCodeFromFixture);
        $this->testCategory($storeCodeFromFixture);

        $this->stopConsumers();
        // create new store
        $newStoreCode = 'new_store';
        $this->createStore($newStoreCode);
        // stop and start consumers
        $this->startConsumers();
        //use case for new storeCode
        $this->testCategory($newStoreCode);
        $this->testProduct($newStoreCode);
    }

    /**
     * Restart consumers.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function startConsumers()
    {
        $logFilePath = TESTS_TEMP_DIR . "/MessageQueueTestLog.txt";
        $params = array_merge_recursive(
            Bootstrap::getInstance()->getAppInitParams(),
            ['MAGE_DIRS' => ['cache' => ['path' => TESTS_TEMP_DIR . '/cache']]]
        );
        /** @var PublisherConsumerController $publisherConsumer */
        $publisherConsumer = Bootstrap::getObjectManager()
            ->create(
                PublisherConsumerController::class,
                [
                    'consumers' => self::CONSUMERS,
                    'appInitParams' => $params,
                    'logFilePath' => $logFilePath,
                ]
            );

        $publisherConsumer->startConsumers();
    }

    /**
     * Stop storefront catalog consumers.
     */
    private function stopConsumers(): void
    {
        $consumersAlias = 'storefront.catalog';
        // kill consumers
        $shell = Bootstrap::getObjectManager()->create(\Magento\Framework\App\Shell::class);
        $consumerProcessIds = $shell->execute("ps ax | grep -v grep | grep '%s' | awk '{print $1}'", [$consumersAlias]);
        if (!empty($consumerProcessIds)) {
            foreach (explode(PHP_EOL, $consumerProcessIds) as $consumerProcessId) {
                $shell->execute("kill {$consumerProcessId}");
                sleep(5);
            }
        }
    }

    /**
     * Test product in store.
     *
     * @param string $storeCodeFromFixture
     * @throws \Exception
     */
    private function testProduct(string $storeCodeFromFixture)
    {
        $productSku = 'simple333';
        $productNameInFixtureStore = 'Simple Product Three';

        $productsQuery = <<<QUERY
{
  products(filter: { sku: { eq: "%s" } }, sort: { name: ASC }) {
    items {
      id
      sku
      name
    }
  }
}
QUERY;
        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery(
            sprintf($productsQuery, $productSku),
            [],
            '',
            $headerMap
        );
        $this->assertCount(
            1,
            $response['products']['items'],
            sprintf('Product with sku "%s" not found in store "%s"', $productSku, $storeCodeFromFixture)
        );
        $this->assertEquals(
            $productNameInFixtureStore,
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );
    }

    /**
     * Test category in store.
     *
     * @param string $storeCodeFromFixture
     * @throws \Exception
     */
    private function testCategory(string $storeCodeFromFixture)
    {
        $categoryName = 'Category 1';
        $categoryQuery = <<<QUERY
{
    categoryList(filters: {name: {match: "%s"}}){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery(
            sprintf($categoryQuery, $categoryName),
            [],
            '',
            $headerMap
        );
        $this->assertCount(
            1,
            $response['categoryList'],
            sprintf('Category with name "%s" not found in store "%s"', $categoryName, $storeCodeFromFixture)
        );
        $this->assertEquals(
            $categoryName,
            $response['categoryList'][0]['name'],
            'Category name in fixture store is invalid.'
        );
    }

    /**
     * Creates store by store code.
     *
     * @param string $storeCode
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createStore(string $storeCode): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

        /** @var \Magento\Store\Model\Store $store */
        $store = $objectManager->create(\Magento\Store\Model\Store::class);

        if (!$store->load($storeCode)->getId()) {
            $store->setCode($storeCode)
                ->setWebsiteId($storeManager->getWebsite()->getId())
                ->setGroupId($storeManager->getWebsite()->getDefaultGroupId())
                ->setName($storeCode)
                ->setSortOrder(10)
                ->setIsActive(1);
            $store->save();

            /** @var $indexer \Magento\Framework\Indexer\IndexerInterface */
            $indexer = $objectManager->create(Indexer::class);
            $indexer->load(IndexerSearch::INDEXER_ID);
            $indexer->reindexAll();
        }
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->removeStore('new_store');
    }

    /**
     * Deletes store by store code.
     *
     * @param string $storeCode
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function removeStore(string $storeCode): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Registry $registry */
        $registry = $objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var \Magento\Store\Model\Store $store */
        $store = $objectManager->get(\Magento\Store\Model\Store::class);
        $store->load($storeCode, 'code');
        if ($store->getId()) {
            $store->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
