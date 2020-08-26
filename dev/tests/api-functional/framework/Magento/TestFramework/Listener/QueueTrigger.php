<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Listener;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Workaround\ConsumerInvoker;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

/**
 * Trigger queue to process storefront consumers
 */
class QueueTrigger implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * Handler for 'startTest' event.
     *
     * Sync Magento monolith App data with Catalog Storefront Storage.
     *
     * @param Test $test
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function startTest(Test $test): void
    {
        if ($test instanceof GraphQlAbstract) {
            $this->waitForAsynchronousResult();
        }
    }

    /**
     * Wait for asynchronous handlers to log data to file.
     *
     * @return void
     *
     * @throws LocalizedException
     */
    private function waitForAsynchronousResult(): void
    {
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = Bootstrap::getObjectManager()->get(ConsumerInvoker::class);
        $consumerInvoker->invoke();
    }
}
