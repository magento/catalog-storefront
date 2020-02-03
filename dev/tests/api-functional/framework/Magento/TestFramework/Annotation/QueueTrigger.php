<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Trigger queue to process storefront consumers
 */
class QueueTrigger
{
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
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function waitForAsynchronousResult(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\StorefrontTestFixer\ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(\Magento\StorefrontTestFixer\ConsumerInvoker::class);
        $consumerInvoker->invoke();
    }
}
