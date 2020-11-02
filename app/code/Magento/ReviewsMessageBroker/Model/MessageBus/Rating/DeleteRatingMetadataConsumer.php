<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsMessageBroker\Model\MessageBus\Rating;

use Magento\CatalogStorefrontApi\Api\Data\DeleteRatingsMetadataRequestMapper;
use Magento\CatalogStorefrontApi\Api\RatingsMetadataServerInterface;
use Magento\ReviewsMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete rating metadata from storage
 */
class DeleteRatingMetadataConsumer implements ConsumerEventInterface
{
    /**
     * @var DeleteRatingsMetadataRequestMapper
     */
    private $deleteRatingsMetadataRequestMapper;

    /**
     * @var RatingsMetadataServerInterface
     */
    private $ratingsMetadataServer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeleteRatingsMetadataRequestMapper $deleteRatingsMetadataRequestMapper
     * @param RatingsMetadataServerInterface $ratingsMetadataServer
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteRatingsMetadataRequestMapper $deleteRatingsMetadataRequestMapper,
        RatingsMetadataServerInterface $ratingsMetadataServer,
        LoggerInterface $logger
    ) {
        $this->deleteRatingsMetadataRequestMapper = $deleteRatingsMetadataRequestMapper;
        $this->ratingsMetadataServer = $ratingsMetadataServer;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities, string $scope = null): void
    {
        $ids = [];

        foreach ($entities as $entity) {
            $ids[] = $entity->getEntityId();
        }

        $deleteRequest = $this->deleteRatingsMetadataRequestMapper->setData(
            [
                'ratingIds' => $ids,
                'store' => $scope,
            ]
        )->build();
        $result = $this->ratingsMetadataServer->DeleteRatingsMetadata($deleteRequest);

        if ($result->getStatus() === false) {
            $this->logger->error(\sprintf('Rating metadata deletion has failed: "%s"', $result->getMessage()));
        }
    }
}
