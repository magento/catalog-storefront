<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * @inheritDoc
 */
class QueueTrigger
{
    /**
     * @var int
     */
    private $maxMessages = 500;

    /**
     * Handler for 'startTest' event.
     *
     * Sync Magento monolith App data with Catalog Storefront Storage.
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        if ($test instanceof GraphQlAbstract) {
            $this->waitForAsynchronousResult();
        }
    }

    /**
     * Wait for asynchronous handlers to log data to file.
     *
     * @param int $expectedLinesCount
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function waitForAsynchronousResult(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\MessageQueue\ConsumerFactory $consumerFactory */
        $consumerFactory = $objectManager->create(\Magento\Framework\MessageQueue\ConsumerFactory::class);
        $consumers = [
            'storefront.catalog.category.update',
            'storefront.catalog.product.update',
            'storefront.catalog.data.consume',
        ];
        foreach ($consumers as $consumerName) {
            $consumer = $consumerFactory->get($consumerName, 1000);
            $consumer->process($this->maxMessages);
        }
    }
}
