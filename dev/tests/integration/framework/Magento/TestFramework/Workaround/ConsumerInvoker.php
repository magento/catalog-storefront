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
        'catalog.product.export.consumer',
        'catalog.product.variants.export.consumer',
        'catalog.category.export.consumer',
        'export.product.reviews.consumer',
        'export.rating.metadata.consumer',
    ];

    /**
     * Invoke consumers
     *
     * @param array $consumersToProcess
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function invoke(array $consumersToProcess = []): void
    {
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
