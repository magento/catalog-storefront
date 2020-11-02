<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsStorefront\Test\Api;

use Magento\CatalogStorefront\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\Framework\Amqp\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Workaround\ConsumerInvoker;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use PHPUnit\Framework\TestResult;

/**
 * Test abstract class for store front tests
 * Storefront API tests should be run as WebAPI test due to Message Broker do a REST call to the Export API to receive
 * reviews and ratings data.
 */
abstract class StorefrontTestsAbstract extends TestCase
{
    /**
     * List of reviews & ratings metadata feed names
     *
     * @var array
     */
    private const FEEDS = [
        'catalog_data_exporter_product_reviews',
        'catalog_data_exporter_rating_metadata',
    ];

    /**
     * List of queues used for reviews & ratings metadata
     *
     * @var array
     */
    private const QUEUES = [
        'export.product.reviews.queue',
        'export.rating.metadata.queue',
    ];

    /**
     * List of reviews & ratings metadata consumers
     *
     * @var array
     */
    private const CONSUMERS = [
        'export.product.reviews.consumer',
        'export.rating.metadata.consumer',
    ];

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
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }

    /**
     * Review & rating storage and feeds are need to be cleared after test execution to prevent "dependency" tests fail
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearCatalogStorage();
        $this->cleanFeeds();
        $this->cleanOldMessages();
    }

    /**
     * @inheritdoc
     */
    public function run(TestResult $result = null): TestResult
    {
        $this->cleanOldMessages();

        return parent::run($result);
    }

    /**
     * Remove review & rating storage to prevent data duplication in tests
     */
    private function clearCatalogStorage(): void
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $availableStores = $storeManager->getStores();

        $this->deleteDataSource('review', null);
        foreach ($availableStores as $store) {
            $this->deleteDataSource('rating_metadata', $store->getCode());
        }
    }

    /**
     * Delete data source
     *
     * @param string $entityType
     * @param string|null $storeCode
     *
     * @return void
     */
    private function deleteDataSource(string $entityType, ?string $storeCode): void
    {
        /** @var DataDefinitionInterface $dataDefinition */
        $dataDefinition = Bootstrap::getObjectManager()->get(DataDefinitionInterface::class);
        /** @var State $storageState */
        $storageState = Bootstrap::getObjectManager()->get(State::class);

        try {
            $sourceName = $storageState->getCurrentDataSourceName([$storeCode, $entityType]);
            $dataDefinition->deleteDataSource($sourceName);
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
        $resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
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
     *
     * @throws \LogicException
     * @throws AMQPTimeoutException
     */
    private function cleanOldMessages(): void
    {
        if ($this->isSoap()) {
            return;
        }

        /** @var Config $amqpConfig */
        $amqpConfig = Bootstrap::getObjectManager()->get(Config::class);

        foreach (self::QUEUES as $queue) {
            $amqpConfig->getChannel()->queue_purge($queue);
        }
    }

    /**
     * Runs consumers before test execution
     *
     * @throws \Throwable
     */
    protected function runTest()
    {
        if (!$this->isSoap()) {
            Bootstrap::getObjectManager()->create(ConsumerInvoker::class)->invoke(self::CONSUMERS);
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
     * Compare expected and actual results
     *
     * @param array $expected
     * @param array $actual
     * @param string|null $message
     *
     * @throws AssertionFailedError
     */
    protected function compare(array $expected, array $actual, string $message = null): void
    {
        $diff = $this->compareArraysRecursively->execute($expected, $actual);

        if (!empty($diff)) {
            $message = $message ?? "Actual response doesn't equal expected data";
            $message .= "\n Diff:\n" . var_export($diff, true);
            $message .= "\n Actual:\n" . var_export($actual, true);
            self::fail($message);
        }
    }
}
