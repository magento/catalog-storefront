<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsMessageBroker\Model\MessageBus\Review;

use Magento\CatalogStorefrontApi\Api\Data\DeleteReviewsRequestMapper;
use Magento\CatalogStorefrontApi\Api\ProductReviewsServerInterface;
use Magento\ReviewsMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete reviews from storage
 */
class DeleteReviewsConsumer implements ConsumerEventInterface
{
    /**
     * @var DeleteReviewsRequestMapper
     */
    private $deleteReviewsRequestMapper;

    /**
     * @var ProductReviewsServerInterface
     */
    private $productReviewsServer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeleteReviewsRequestMapper $deleteReviewsRequestMapper
     * @param ProductReviewsServerInterface $productReviewsServer
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteReviewsRequestMapper $deleteReviewsRequestMapper,
        ProductReviewsServerInterface $productReviewsServer,
        LoggerInterface $logger
    ) {
        $this->deleteReviewsRequestMapper = $deleteReviewsRequestMapper;
        $this->productReviewsServer = $productReviewsServer;
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

        $deleteRequest = $this->deleteReviewsRequestMapper->setData(['reviewIds' => $ids])->build();
        $result = $this->productReviewsServer->DeleteProductReviews($deleteRequest);

        if ($result->getStatus() === false) {
            $this->logger->error(\sprintf('Reviews deletion has failed: "%s"', $result->getMessage()));
        }
    }
}
