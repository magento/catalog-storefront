<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;

/**
 * Override \Magento\Framework\MessageQueue\UseCase\MixSyncAndAsyncSingleQueueTest
 * Should be eliminated after resolving MC-32269 for 2.4
 */
class MixSyncAndAsyncSingleQueueTest extends QueueTestCaseAbstract
{
    /**
     * @var \Magento\TestModuleAsyncAmqp\Model\AsyncTestData
     */
    protected $msgObject;

    /**
     * {@inheritdoc}
     */
    protected $consumers = ['mixed.sync.and.async.queue.consumer'];

    /**
     * @var string[]
     */
    protected $messages = ['message1', 'message2', 'message3'];

    /**
     * @var int
     */
    protected $maxMessages = 4;

    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->config = $this->loadConfig();
    }

    public function testMixSyncAndAsyncSingleQueue()
    {
        $this->publisherConsumerController->stopConsumers();

        $config = $this->config;
        $config['queue']['consumers_wait_for_messages'] = 1;
        $this->writeConfig($config);
        $this->assertArraySubset(['queue' => ['consumers_wait_for_messages' => 1]], $this->loadConfig());

        $this->msgObject = $this->objectManager->create(\Magento\TestModuleAsyncAmqp\Model\AsyncTestData::class);

        $this->publisherConsumerController->startConsumers();

        // Publish asynchronous messages
        foreach ($this->messages as $item) {
            $this->msgObject->setValue($item);
            $this->msgObject->setTextFilePath($this->logFilePath);
            $this->publisher->publish('multi.topic.queue.topic.c', $this->msgObject);
        }

        // Publish synchronous message to the same queue
        $input = 'Input value';
        $response = $this->publisher->publish('sync.topic.for.mixed.sync.and.async.queue', $input);
        $this->assertEquals($input . ' processed by RPC handler', $response);

        $this->waitForAsynchronousResult(count($this->messages), $this->logFilePath);

        // Verify that asynchronous messages were processed
        foreach ($this->messages as $item) {
            $this->assertContains($item, file_get_contents($this->logFilePath));
        }

        $this->rollbackConsumerWaitConfig();
    }

    /**
     * To prevent failure \Magento\TestFramework\Isolation\DeploymentConfig::endTest which executed before tearDown
     */
    private function rollbackConsumerWaitConfig()
    {
        $this->writeConfig($this->config);
    }

    /**
     * @return array
     */
    private function loadConfig(): array
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * @param array $config
     */
    private function writeConfig(array $config): void
    {
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_ENV => $config], true);
    }
}
