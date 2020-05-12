<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Indexer;

use Magento\CatalogDataExporter\Model\Indexer\IndexerCallbackInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\App\DeploymentConfig;

class IndexerCallback implements IndexerCallbackInterface
{
    private const BATCH_SIZE = 100;

    private const TOPIC_NAME = 'catalog.export.product.data';

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param PublisherInterface $queuePublisher
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        PublisherInterface $queuePublisher,
        \DeploymentConfig $deploymentConfig
    ) {
        $this->queuePublisher = $queuePublisher;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids)
    {
        foreach (array_chunk($ids, $this->getBatchSize()) as $idsChunk) {
            if (!empty($idsChunk)) {
                $this->queuePublisher->publish(self::TOPIC_NAME, $idsChunk);
            }
        }
    }

    /**
     * Get batch size
     *
     * @return int
     */
    private function getBatchSize()
    {
        $batchSize = (int) $this->deploymentConfig->get('catalog_export/batch_size');
        return $batchSize ?: self::BATCH_SIZE;
    }
}
