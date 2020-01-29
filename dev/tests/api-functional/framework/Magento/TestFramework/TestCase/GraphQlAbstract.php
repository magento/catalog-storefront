<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class GraphQlAbstract extends WebapiAbstract
{
    /**
     * @var string[]
     */
    protected $consumers = [];

    /**
     * @var PublisherInterface
     */
    private static $publisher;

    /**
     * @var PublisherConsumerController
     */
    protected static $publisherConsumerController;

    /**
     * Initialize fixture namespaces.
     * //phpcs:disable
     */
    public static function setUpBeforeClass()
    {
        //phpcs:enable
        parent::setUpBeforeClass();

        $objectManager = Bootstrap::getObjectManager();
        self::$publisherConsumerController = $objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers' => [
                    'storefront.catalog.data.consume',
                    'storefront.catalog.category.update',
                    'storefront.catalog.product.update',
                ],
                'logFilePath' => '',
                'appInitParams' => \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams()
            ]
        );

        try {
            self::$publisherConsumerController->initializeWithoutSleep();
        } catch (EnvironmentPreconditionException $e) {
            self::markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            self::fail(
                $e->getMessage()
            );
        }
        self::$publisher = self::$publisherConsumerController->getPublisher();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        self::$publisherConsumerController->stopConsumers();
        $this->clearCatalogStorage();
    }

    /**
     * Workaround for https://bugs.php.net/bug.php?id=72286
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function tearDownAfterClass()
    {
        // phpcs:enable Magento2.Functions.StaticFunction
        if (version_compare(phpversion(), '7') == -1) {
            $closeConnection = new \ReflectionMethod(\Magento\Amqp\Model\Config::class, 'closeConnection');
            $closeConnection->setAccessible(true);

            $config = Bootstrap::getObjectManager()->get(\Magento\Amqp\Model\Config::class);
            $closeConnection->invoke($config);
        }
    }

    /**
     * Remove catalog storages to prevent data duplication in tests
     */
    private function clearCatalogStorage(): void
    {
        /** @var \Magento\CatalogProduct\Model\Storage\Client\DataDefinitionInterface $dataDefinition */
        $dataDefinition = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\CatalogProduct\Model\Storage\Client\DataDefinitionInterface::class
        );
        /** @var \Magento\CatalogProduct\Model\Storage\State $storageState */
        $storageState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\CatalogProduct\Model\Storage\State::class
        );
        $entityTypes = ['category', 'product'];
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Store\Model\StoreManagerInterface::class);
        $availableStores = $storeManager->getStores();
        foreach ($entityTypes as $entityType) {
            foreach ($availableStores as $store) {
                try {
                    $sourceName = $storageState->getCurrentDataSourceName([$store->getId(), $entityType]);
                    $dataDefinition->deleteDataSource($sourceName);

                } catch (\Exception $e) {
                    // Do nothing if no source
                }
            }

        }
    }

    /**
     * ============================================================================
     * ||                                                                        ||
     * ||  Magento\TestFramework\TestCase\GraphQlAbstract part of functionality: ||
     * ||                                                                        ||
     * ============================================================================
     */

    /**
     * The instantiated GraphQL client.
     *
     * @var \Magento\TestFramework\TestCase\GraphQl\Client
     */
    private $graphQlClient;

    /**
     * @var \Magento\Framework\App\Cache
     */
    private $appCache;

    /**
     * Perform GraphQL query call via GET to the system under test.
     *
     * @see \Magento\TestFramework\TestCase\GraphQl\Client::call()
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return array|int|string|float|bool GraphQL call results
     * @throws \Exception
     */
    public function graphQlQuery(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = []
    ) {
        return $this->getGraphQlClient()->get(
            $query,
            $variables,
            $operationName,
            $this->composeHeaders($headers)
        );
    }

    /**
     * Perform GraphQL mutations call via POST to the system under test.
     *
     * @see \Magento\TestFramework\TestCase\GraphQl\Client::call()
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return array|int|string|float|bool GraphQL call results
     * @throws \Exception
     */
    public function graphQlMutation(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = []
    ) {
        return $this->getGraphQlClient()->post(
            $query,
            $variables,
            $operationName,
            $this->composeHeaders($headers)
        );
    }

    /**
     * Perform GraphQL query via GET and returns only the response headers
     *
     * @param string $query
     * @param array $variables
     * @param string $operationName
     * @param array $headers
     * @return array
     */
    public function graphQlQueryWithResponseHeaders(
        string $query,
        array $variables = [],
        string $operationName = '',
        array $headers = []
    ): array {
        return $this->getGraphQlClient()->getWithResponseHeaders(
            $query,
            $variables,
            $operationName,
            $this->composeHeaders($headers)
        );
    }

    /**
     * Compose headers
     *
     * @param array $headers
     * @return string[]
     */
    private function composeHeaders(array $headers): array
    {
        $headersArray = [];
        foreach ($headers as $key => $value) {
            $headersArray[] = sprintf('%s: %s', $key, $value);
        }
        return $headersArray;
    }

    /**
     * Clear cache so integration test can alter cached GraphQL schema
     *
     * @return bool
     */
    protected function cleanCache()
    {
        return $this->getAppCache()->clean(\Magento\Framework\App\Config::CACHE_TAG);
    }

    /**
     * Return app cache setup.
     *
     * @return \Magento\Framework\App\Cache
     */
    private function getAppCache()
    {
        if (null === $this->appCache) {
            $this->appCache = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache::class);
        }
        return $this->appCache;
    }

    /**
     * Get GraphQL adapter (create if requested one does not exist).
     *
     * @return \Magento\TestFramework\TestCase\GraphQl\Client
     */
    private function getGraphQlClient()
    {
        if ($this->graphQlClient === null) {
            $this->graphQlClient = Bootstrap::getObjectManager()->get(
                \Magento\TestFramework\TestCase\GraphQl\Client::class
            );
        }
        return $this->graphQlClient;
    }

    /**
     * Compare actual response fields with expected
     *
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    protected function assertResponseFields($actualResponse, $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            self::assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            self::assertEquals(
                $expectedValue,
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }
}
