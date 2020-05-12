<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Indexer\IndexerCallbackInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

class IndexerCallback implements IndexerCallbackInterface
{
    private const BATCH_SIZE = 100;

    private const TOPIC_NAME = 'catalog.export.product.data';

    private $queuePublisher;

    /**
     * @param PublisherInterface $queuePublisher
     */
    public function __construct(
        PublisherInterface $queuePublisher
    ) {
        $this->queuePublisher = $queuePublisher;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids)
    {
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                // @todo understand why string[] doesn't work
                $this->queuePublisher->publish(self::TOPIC_NAME, json_encode($idsChunk));
            }
        }
    }
}
