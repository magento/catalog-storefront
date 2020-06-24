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
    private const BATCHSIZE = 1000;

    /**
     * List of storefront consumers
     */
    private const CONSUMERS = [
        'storefront.catalog.category.update',
        'storefront.catalog.product.update',
        'storefront.catalog.data.consume',
        'catalog.export.consumer',
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
}
