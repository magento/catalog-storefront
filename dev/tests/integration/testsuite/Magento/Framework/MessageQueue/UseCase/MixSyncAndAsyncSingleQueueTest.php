<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

/**
 * Test sync and async message processing.
 *
 * @magentoConfigFixture default_store queue/consumers_wait_for_messages 1
 */
class MixSyncAndAsyncSingleQueueTest extends QueueTestCaseAbstract
{
    /**
     * @var AsyncTestData
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

    public function testMixSyncAndAsyncSingleQueue()
    {
        $this->markTestSkipped('This test requires consumers_wait_for_messages to be set to 1.
            That is contradict with Catalog Store front initial configuration fro integration test.
            And impossible to change on a fly.');

        $this->msgObject = $this->objectManager->create(AsyncTestData::class); // @phpstan-ignore-line

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
            $this->assertStringContainsString($item, file_get_contents($this->logFilePath));
        }
    }
}
