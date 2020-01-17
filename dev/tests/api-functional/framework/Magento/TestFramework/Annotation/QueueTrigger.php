<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;

/**
 * @inheritDoc
 */
class QueueTrigger
{
    /**
     * @var string
     */
    private $logFilePath = TESTS_TEMP_DIR . "/CatalogStorefrontMessageQueueTestLog.txt";

    /**
     * @var int|null
     */
    private $maxMessages = 500;

    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    /**
     * Handler for 'startTest' event.
     * Sync Magento monolith App data with Catalog Storefront Storage.
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @throws PreconditionFailedException
     * @return void
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $this->waitForAsynchronousResult(1);
    }

    /**
     * Wait for asynchronous handlers to log data to file.
     *
     * @param int $expectedLinesCount
     * @throws PreconditionFailedException
     */
    private function waitForAsynchronousResult($expectedLinesCount)
    {
        sleep(5);
        //$expectedLinesCount, $logFilePath
//        $this->getPublisherConsumerController()->waitForAsynchronousResult(
//            [$this, 'checkLogsExists'],
//            [$expectedLinesCount, $this->logFilePath]
//        );
    }

    private function getPublisherConsumerController()
    {
        if (null == $this->publisherConsumerController) {
            $objectManager = Bootstrap::getObjectManager();
            $this->publisherConsumerController = $objectManager->create(
                PublisherConsumerController::class,
                [
                    'consumers' => [
                        'storefront.catalog.data.consume',
                        'storefront.catalog.category.update',
                        'storefront.catalog.product.update',
                    ],
                    'logFilePath' => $this->logFilePath,
                    'maxMessages' => $this->maxMessages,
                    'appInitParams' => \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams()
                ]
            );
        }

        return $this->publisherConsumerController;
    }

    /**
     * Checks that logs exist
     *
     * @param int $expectedLinesCount
     * @return bool
     */
    public function checkLogsExists($expectedLinesCount)
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $actualCount = file_exists($this->logFilePath) ? count(file($this->logFilePath)) : 0;
        return $expectedLinesCount === $actualCount;
    }
}
