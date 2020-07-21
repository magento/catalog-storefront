<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Invoke consumers to push data from Magento Monolith to Catalog Storefront service
 */
class ConsumerInvoker
{
    /**
     * Batch size
     */
    private const BATCHSIZE = 10000;

    /**
     * List of storefront consumers
     */
    private const CONSUMERS = [
        'storefront.catalog.product.update',
        'storefront.catalog.data.consume',
        'catalog.product.export.consumer',
        'catalog.category.export.consumer',
        'storefront.catalog.category.update'
    ];

    /**
     * Invoke consumers
     *
     * @param bool $invokeInTestsOnly
     * @param array $consumersToProcess
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \ReflectionException
     */
    public function invoke($invokeInTestsOnly = false, $consumersToProcess = []): void
    {
        if ($invokeInTestsOnly) {
            $trace = (new \Exception())->getTraceAsString();
            if (false === strpos($trace, 'Magento\GraphQl')
                || false === strpos($trace, 'src/Framework/TestCase.php')
                || false !== strpos($trace, 'ApiDataFixture->startTest')) {
                return;
            }
        }
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\MessageQueue\ConsumerFactory $consumerFactory */
        $consumerFactory = $objectManager->create(\Magento\Framework\MessageQueue\ConsumerFactory::class);
        $consumersToProcess = $consumersToProcess ?: self::CONSUMERS;

        foreach ($consumersToProcess as $consumerName) {
            $consumer = $consumerFactory->get($consumerName, self::BATCHSIZE);
            $consumer->process(self::BATCHSIZE);
        }
    }

    /**
     * Invoke specific size of events
     *
     * @param int $size
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function invokeSpecificSize(int $size): void
    {
        $invokeInTestsOnly = false;
        $consumersToProcess = [];

        if ($invokeInTestsOnly) {
            $trace = (new \Exception())->getTraceAsString();
            if (false === strpos($trace, 'Magento\GraphQl')
                || false === strpos($trace, 'src/Framework/TestCase.php')
                || false !== strpos($trace, 'ApiDataFixture->startTest')) {
                return;
            }
        }
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\MessageQueue\ConsumerFactory $consumerFactory */
        $consumerFactory = $objectManager->create(\Magento\Framework\MessageQueue\ConsumerFactory::class);
        $consumersToProcess = $consumersToProcess ?: self::CONSUMERS;

        foreach ($consumersToProcess as $consumerName) {
            $consumer = $consumerFactory->get($consumerName, $size);
            $consumer->process($size);
        }
    }
}
