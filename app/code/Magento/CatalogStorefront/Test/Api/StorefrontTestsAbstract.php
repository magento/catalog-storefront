<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api;

use Magento\CatalogStorefront\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Workaround\ConsumerInvoker;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use PHPUnit\Framework\TestResult;

/**
 * Test abstract class for store front tests
 * Storefront API tests should be run as WebAPI test due to Message Broker do a REST call to the Export API to receive
 * catalog data.
 */
abstract class StorefrontTestsAbstract extends TestCase
{
    /**
     * @var array
     */
    private const FEEDS = [
        'catalog_data_exporter_categories',
        'catalog_data_exporter_products',
        'catalog_data_exporter_product_variants'
    ];

    /**
     * @var array
     */
    private const QUEUES = [
        'catalog.category.export.queue',
        'catalog.product.export.queue',
        'catalog.product.variants.export.queue'
    ];

    /**
     * @var CompareArraysRecursively
     */
    private $compareArraysRecursively;

    /**
     * @var DataDefinitionInterface
     */
    private $dataDefinition;

    /**
     * @var State
     */
    private $storageState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
        $this->dataDefinition = Bootstrap::getObjectManager()->get(DataDefinitionInterface::class);
        $this->storageState = Bootstrap::getObjectManager()->get(State::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * Catalog storage and feeds are need to be cleared after test execution to prevent "dependency" tests fail
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearCatalogStorage();
        $this->cleanFeeds();
        $this->cleanOldMessages();
    }

    /**
     * Remove catalog storage to prevent data duplication in tests
     */
    private function clearCatalogStorage(): void
    {
        $entityTypes = ['category', 'product', 'product_variant'];
        $availableStores = $this->storeManager->getStores();

        foreach ($entityTypes as $entityType) {
            if ($entityType === 'product_variant') {
                $this->deleteDataSource('', $entityType);
            } else {
                foreach ($availableStores as $store) {
                    $this->deleteDataSource($store->getCode(), $entityType);
                }
            }
        }
    }

    /**
     * Delete data source
     *
     * @param string $scope
     * @param string $entityType
     */
    private function deleteDataSource(string $scope, string $entityType): void
    {
        try {
            $sourceName = $this->storageState->getCurrentDataSourceName([$scope, $entityType]);
            $this->dataDefinition->deleteDataSource($sourceName);
        } catch (\Exception $e) {
            // Do nothing if no source
        }
    }

    /**
     * On each tear down we need to clean all feed data
     *
     * @return void
     */
    private function cleanFeeds(): void
    {
        /** @var ResourceConnection $resourceConnection */
        $resourceConnection = Bootstrap::getObjectManager()
            ->get(ResourceConnection::class);
        $connection = $resourceConnection->getConnection();

        foreach (self::FEEDS as $feed) {
            $feedTable = $resourceConnection->getTableName($feed);
            $connection->delete($feedTable);
        }
    }

    /**
     * Clean old messages from rabbitmq
     *
     * @return void
     */
    private function cleanOldMessages(): void
    {
        if ($this->isSoap()) {
            return;
        }

        /** @var \Magento\Framework\Amqp\Config $amqpConfig */
        $amqpConfig = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Amqp\Config::class);

        foreach (self::QUEUES as $queue) {
            $amqpConfig->getChannel()->queue_purge($queue);
        }
    }

    /**
     * Run tests and clean old messages before running other tests
     *
     * @param TestResult|null $result
     * @return TestResult
     */
    public function run(TestResult $result = null): TestResult
    {
        $this->cleanOldMessages();
        $this->resetIndexerToOnSave();
        return parent::run($result);
    }

    /**
     * Runs consumers before test execution
     *
     * @throws \Throwable
     */
    protected function runTest()
    {
        if (!$this->isSoap()) {
            $this->runConsumers();
            parent::runTest();
        }
    }

    /**
     * Check if it is SOAP request
     *
     * @return bool
     */
    private function isSoap(): bool
    {
        return TESTS_WEB_API_ADAPTER === 'soap';
    }

    /**
     * Run all/selected consumers
     *
     * @param array $consumers
     */
    public function runConsumers(array $consumers = []) : void
    {
        $consumerInvoker = Bootstrap::getObjectManager()->create(ConsumerInvoker::class);
        $consumerInvoker->invoke($consumers);
    }

    /**
     * Resetting indexer to 'on save' mode
     *
     * @return void
     */
    private function resetIndexerToOnSave(): void
    {
        $indexer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(Indexer::class);
        $indexer->load('catalog_data_exporter_products');
        $indexer->setScheduled(false);
        $indexer->load('catalog_data_exporter_product_variants');
        $indexer->setScheduled(false);
    }

    /**
     * Compare expected and actual results
     *
     * @param array $expected
     * @param array $actual
     * @param string|null $message
     */
    protected function compare(array $expected, array $actual, string $message = null): void
    {
        $diff = $this->compareArraysRecursively->execute(
            $expected,
            $actual
        );
        if (!empty($diff)) {
            $message = $message ?? "Actual response doesn't equal expected data";
            $message .= "\n Diff:\n" . var_export($diff, true);
            $message .= "\n Actual:\n" . var_export($actual, true);
            self::fail($message);
        }
    }
}
