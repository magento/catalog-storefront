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
     * @var int
     */
    private $maxMessages = 500;

    /**
     * Process storefront consumers during test setup
     *
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $consumers = [
            'storefront.catalog.category.update',
            'storefront.catalog.product.update',
            'storefront.catalog.data.consume',
        ];
        $this->processCatalogQueueMessages($consumers);
    }

    /**
     * Process provided consumers list
     *
     * @param $consumers
     * @throws LocalizedException
     */
    protected function processCatalogQueueMessages(array $consumers): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerFactory $consumerFactory */
        $consumerFactory = $objectManager->create(ConsumerFactory::class);
        foreach ($consumers as $consumerName) {
            $consumer = $consumerFactory->get($consumerName, 1000);
            $consumer->process($this->maxMessages);
        }
    }

}
