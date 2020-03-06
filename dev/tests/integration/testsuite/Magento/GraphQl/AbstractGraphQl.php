<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Abstract class for Graphql tests
 *
 * This class running consumers in setUp() and stops them in tearDown()
 */
abstract class AbstractGraphQl extends TestCase
{
    /**
     * Process storefront consumers during test setup
     *
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->processCatalogQueueMessages();
    }

    /**
     * Process provided consumers list
     *
     * @throws LocalizedException
     */
    protected function processCatalogQueueMessages(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\TestFramework\Workaround\ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(\Magento\TestFramework\Workaround\ConsumerInvoker::class);
        $consumerInvoker->invoke();
    }
}
