<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\ConsumerInvoker;
use PHPUnit\Framework\TestCase;

/**
 * Abstract Class for catalog storefront tests
 */
abstract class AbstractCatalogStorefront extends TestCase
{
    /**
     * Process consumers before run test
     *
     * @throws LocalizedException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function runTest()
    {
        $this->processCatalogQueueMessages();
        return parent::runTest();
    }

    /**
     * Invoke consumers to process catalog queue messages
     *
     * @param TestCase $test
     * @throws LocalizedException
     * @throws \ReflectionException
     */
    protected function processCatalogQueueMessages(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke();
    }
}
