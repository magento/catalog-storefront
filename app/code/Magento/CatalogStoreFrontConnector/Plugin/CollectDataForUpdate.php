<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontConnector\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;
use Magento\CatalogStoreFrontConnector\Model\ReindexMessageBuilder;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 */
class CollectDataForUpdate
{
    /**
     * @var PublisherInterface
     */
    private $queuePublisher;
    /**
     * @var ReindexMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var string
     */
    private $topic = 'storefront.collect.reindex.products.data';

    /**
     * @param PublisherInterface $queuePublisher
     * @param ReindexMessageBuilder $messageBuilder
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        ReindexMessageBuilder $messageBuilder
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->messageBuilder = $messageBuilder;
    }

    /**
     *
     * @param Full $subject
     * @param \Closure $proceed
     * @param int $storeId
     * @param int[]|null $productIds
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function aroundRebuildStoreIndex(
        Full $subject,
        \Closure $proceed,
        int $storeId,
        $productIds = []
    ) {
        $result = $proceed($storeId, $productIds);

        $productIds = $productIds ?? [];
        $message = $this->messageBuilder->prepareMessage($storeId, $productIds);
        $this->queuePublisher->publish($this->topic, $message);

        return $result;
    }
}
