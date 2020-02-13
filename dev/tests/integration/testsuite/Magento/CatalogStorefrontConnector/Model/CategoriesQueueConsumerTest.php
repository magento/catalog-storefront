<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\GraphQl\AbstractGraphQl;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\MessageQueue\QueueInterface;

/**
 * Tests for CategoriesQueueConsumer class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoriesQueueConsumerTest extends AbstractGraphQl
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

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
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var Category
     */
    private $categoryResource;

    /**
     * Batch size
     */
    private const BATCHSIZE = 1000;

    /**
     * List of storefront consumers
     */
    private const CONSUMERS = [
        'storefront.catalog.category.update',
        'storefront.catalog.product.update',
        'storefront.catalog.data.consume',
    ];

    /**
     * @throws LocalizedException
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->jsonHelper = $this->objectManager->create(JsonHelper::class);
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->queueRepository = Bootstrap::getObjectManager()->create(QueueRepository::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepository::class);
        $this->categoryResource = $this->objectManager->get(Category::class);
        parent::setUp();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @dataProvider categoryAttributesProvider
     * @param array $expectedData
     *
     * @return void
     * @throws \Exception
     */
    public function testMessageReading(array $expectedData): void
    {
        $this->invoke(self::CONSUMERS);
        $category = $this->categoryRepository->get(333, 1);
        $category->setName('Category New Name');
        $this->categoryResource->save($category);
        $consumersToProcess = [
            'storefront.catalog.category.update'
        ];
        $this->invoke($consumersToProcess);
        $queue = $this->queueRepository->get('amqp', 'storefront.catalog.data.consume');
        $queueBody = $this->getQueueBody($queue);
        $parsedData = $this->jsonHelper->jsonDecode($queueBody);
        $parsedData = $this->jsonHelper->jsonDecode(reset($parsedData));

        foreach ($parsedData['entity_data'] as $attributeKey => $attributeValue) {
            $this->assertEquals($expectedData[$attributeKey], $attributeValue);
        }
    }

    /**
     * DataProvider with expected category data
     *
     * @return array
     */
    public function categoryAttributesProvider(): array
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
                    'name' => 'Category New Name',
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
     * Wait for queue message will be available and get queue body
     *
     * @param QueueInterface $queue
     * @return string|null
     */
    private function getQueueBody(QueueInterface $queue): ?string
    {
        $queueBody = $this->getAsyncMessage($queue);

        if (!$queueBody) {
            self::fail('No asynchronous messages were processed.');
        }

        return $queueBody;
    }

    /**
     * @param QueueInterface $queue
     * @return string|null
     */
    private function getAsyncMessage(QueueInterface $queue): ?string
    {
        $queueBody = null;

        $i = 0;
        do {
            sleep(1);
            $queueBody = \call_user_func_array(
                [$this, 'getMessageBody'],
                [$queue]
            );
        } while (!$queueBody && ($i++ < 10));

        return $queueBody;
    }

    /**
     * Get message body if exists
     *
     * @param QueueInterface $queue
     * @return string|null
     */
    public function getMessageBody($queue): ?string
    {
        $message = $queue->dequeue();

        return $message ? $message->getBody() : null;
    }

    /**
     * Invoke consumers
     *
     * @param array $consumers
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function invoke(array $consumers): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\MessageQueue\ConsumerFactory $consumerFactory */
        $consumerFactory = $objectManager->create(\Magento\Framework\MessageQueue\ConsumerFactory::class);
        foreach ($consumers as $consumerName) {
            $consumer = $consumerFactory->get($consumerName, self::BATCHSIZE);
            $consumer->process(self::BATCHSIZE);
        }
    }
}
