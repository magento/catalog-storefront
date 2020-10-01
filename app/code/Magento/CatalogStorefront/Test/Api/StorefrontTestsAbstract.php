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
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Workaround\ConsumerInvoker;

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
        'catalog_data_exporter_products'
    ];

    /**
     * Catalog storage and feeds are need to be cleared after test execution to prevent "dependency" tests fail
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearCatalogStorage();
        $this->cleanFeeds();
    }

    /**
     * Remove catalog storage to prevent data duplication in tests
     */
    private function clearCatalogStorage(): void
    {
        /** @var DataDefinitionInterface $dataDefinition */
        $dataDefinition = Bootstrap::getObjectManager()->get(
            DataDefinitionInterface::class
        );
        /** @var State $storageState */
        $storageState = Bootstrap::getObjectManager()->get(
            State::class
        );
        $entityTypes = ['category', 'product'];
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = Bootstrap::getObjectManager()
            ->get(\Magento\Store\Model\StoreManagerInterface::class);
        $availableStores = $storeManager->getStores();
        foreach ($entityTypes as $entityType) {
            foreach ($availableStores as $store) {
                try {
                    $sourceName = $storageState->getCurrentDataSourceName([$store->getCode(), $entityType]);
                    $dataDefinition->deleteDataSource($sourceName);
                } catch (\Exception $e) {
                    // Do nothing if no source
                }
            }
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
     * Runs consumers before test execution
     *
     * @throws \Throwable
     */
    protected function runTest()
    {
        $this->runConsumers();
        parent::runTest();
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
}
