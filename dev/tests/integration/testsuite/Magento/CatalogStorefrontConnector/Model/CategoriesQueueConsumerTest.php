<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CategoriesQueueConsumer class
 */
class CategoriesQueueConsumerTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    private $indexer;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var array
     */
    private $consumers = ['storefront_categories_consumer'];

    /**
     * @var array
     */
    private $indexers = [
        'catalog_category_product',
        'catalogsearch_fulltext'
    ];

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->jsonHelper = $this->objectManager->create(JsonHelper::class);
        $this->indexer = Bootstrap::getObjectManager()->create(
            Indexer::class
        );
        $this->queueRepository = Bootstrap::getObjectManager()->create(
            QueueRepository::class
        );
        $this->publisherConsumerController = Bootstrap::getObjectManager()->create(
            PublisherConsumerController::class,
            [
                'consumers' => $this->consumers,
                'logFilePath' => '',
                'maxMessages' => null,
                'appInitParams' => Bootstrap::getInstance()->getAppInitParams()
            ]
        );
        $this->initPublisherController();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @dataProvider categoryAttributesProvider
     * @param array $expectedData
     *
     * @throws \Exception
     */
    public function testMessageReading(array $expectedData)
    {
        $this->reindex();
        $queue = $this->queueRepository->get('amqp', 'storefront.entities.update');
        $queueBody = $this->waitForAsynchronousResult($queue);
        $parsedData = $this->jsonHelper->jsonDecode($queueBody);
        $parsedData = array_pop($parsedData);
        $entityData = $this->jsonHelper->jsonDecode($parsedData['entity_data']);

        foreach ($entityData as $attributeKey => $attributeValue) {
            $this->assertEquals($expectedData[$attributeKey], $attributeValue);
        }
    }

    /**
     * DataProvider with expected category data
     *
     * @return array
     */
    public function categoryAttributesProvider()
    {
        return [
            'category_with_simple_product' => [
                [
                    'children_count' => '0',
                    'level' => '2',
                    'path' => '1/2/3',
                    'position' => '1',
                    'id' => '333',
                    'available_sort_by' => ['position'],
                    'is_active' => '1',
                    'is_anchor' => '1',
                    'include_in_menu' => '1',
                    'name' => 'Category 1',
                    'default_sort_by' => 'name',
                    'url_key' => 'category-1',
                    'url_path' => 'category-1',
                    'description' => null,
                    'canonical_url' => null,
                    'product_count' => '1',
                    'children' => []
                ]
            ]
        ];
    }

    /**
     * Wait for queue message will be available
     *
     * \Magento\Framework\MessageQueue\QueueInterface $queue
     */
    private function waitForAsynchronousResult($queue)
    {
        $queueBody = null;
        $i = 0;
        do {
            sleep(1);
            $queueBody = call_user_func_array(
                [$this, 'getMessageBody'],
                [$queue]
            );
        } while (!$queueBody && ($i++ < 180));

        if (!$queueBody) {
            $this->fail('No asynchronous messages were processed.');
        }

        return $queueBody;
    }

    /**
     * Get message body if exists
     *
     * @param \Magento\Framework\MessageQueue\QueueInterface $queue
     * @return string|null
     */
    public function getMessageBody($queue)
    {
        $message = $queue->dequeue();
        $messageBody = $message ? $message->getBody() : null;

        return $messageBody;
    }

    /**
     * Make reindex for indexers stored in $this->indexer var
     *
     * @throws \Exception
     */
    private function reindex()
    {
        foreach ($this->indexers as $indexer) {
            $this->indexer->load($indexer);
            $this->indexer->reindexAll();
        }
    }

    /**
     * Init AMQP publisher
     */
    private function initPublisherController()
    {
        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }
    }

    /**
     * Tear down after tests
     */
    public function tearDown()
    {
        $this->publisherConsumerController->stopConsumers();

        parent::tearDown();
    }
}
