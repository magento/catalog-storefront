<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReviewsMessageBroker\Model\MessageBus\Rating;

use Magento\CatalogStorefrontApi\Api\Data\ImportRatingsMetadataRequestMapper;
use Magento\CatalogStorefrontApi\Api\RatingsMetadataServerInterface;
use Magento\ReviewsMessageBroker\Model\FetchRatingsMetadataInterface;
use Magento\ReviewsMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish ratings metadata into storage
 */
class PublishRatingMetadataConsumer implements ConsumerEventInterface
{
    /**
     * @var FetchRatingsMetadataInterface
     */
    private $fetchRatingsMetadata;

    /**
     * @var ImportRatingsMetadataRequestMapper
     */
    private $importRatingsMetadataRequestMapper;

    /**
     * @var RatingsMetadataServerInterface
     */
    private $ratingsMetadataServer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param FetchRatingsMetadataInterface $fetchRatingsMetadata
     * @param ImportRatingsMetadataRequestMapper $importRatingsMetadataRequestMapper
     * @param RatingsMetadataServerInterface $ratingsMetadataServer
     * @param LoggerInterface $logger
     */
    public function __construct(
        FetchRatingsMetadataInterface $fetchRatingsMetadata,
        ImportRatingsMetadataRequestMapper $importRatingsMetadataRequestMapper,
        RatingsMetadataServerInterface $ratingsMetadataServer,
        LoggerInterface $logger
    ) {
        $this->fetchRatingsMetadata = $fetchRatingsMetadata;
        $this->importRatingsMetadataRequestMapper = $importRatingsMetadataRequestMapper;
        $this->ratingsMetadataServer = $ratingsMetadataServer;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities, string $scope = null): void
    {
        $ratingsMetadata = $this->fetchRatingsMetadata->execute($entities, $scope);

        foreach ($ratingsMetadata as &$data) {
            $data['id'] = $data['rating_id'];
        }

        $importRequest = $this->importRatingsMetadataRequestMapper->setData(
            [
                'metadata' => $ratingsMetadata,
                'store' => $scope,
            ]
        )->build();
        $importResult = $this->ratingsMetadataServer->ImportRatingsMetadata($importRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(\sprintf('Ratings metadata import is failed: "%s"', $importResult->getMessage()));
        }
    }
}
