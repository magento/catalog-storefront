<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReviewsMessageBroker\Model\MessageBus\Review;

use Magento\CatalogStorefrontApi\Api\Data\ImportReviewsRequestMapper;
use Magento\CatalogStorefrontApi\Api\ProductReviewsServerInterface;
use Magento\ReviewsMessageBroker\Model\FetchReviewsInterface;
use Magento\ReviewsMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish reviews into storage
 */
class PublishReviewsConsumer implements ConsumerEventInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ImportReviewsRequestMapper
     */
    private $importReviewsRequestMapper;

    /**
     * @var ProductReviewsServerInterface
     */
    private $productReviewsServer;

    /**
     * @var FetchReviewsInterface
     */
    private $fetchReviews;

    /**
     * @param FetchReviewsInterface $fetchReviews
     * @param ImportReviewsRequestMapper $importReviewsRequestMapper
     * @param ProductReviewsServerInterface $productReviewsServer
     * @param LoggerInterface $logger
     */
    public function __construct(
        FetchReviewsInterface $fetchReviews,
        ImportReviewsRequestMapper $importReviewsRequestMapper,
        ProductReviewsServerInterface $productReviewsServer,
        LoggerInterface $logger
    ) {
        $this->fetchReviews = $fetchReviews;
        $this->importReviewsRequestMapper = $importReviewsRequestMapper;
        $this->productReviewsServer = $productReviewsServer;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities, string $scope = null): void
    {
        $reviewsData = $this->fetchReviews->execute($entities);

        foreach ($reviewsData as &$data) {
            $data['id'] = $data['review_id'];

            // TODO change id to rating_id in proto
            foreach ($data['ratings'] ?? [] as $key => $rating) {
                $data['ratings'][$key]['id'] = $rating['rating_id'];
            }
        }

        $importRequest = $this->importReviewsRequestMapper->setData(['reviews' => $reviewsData])->build();
        $importResult = $this->productReviewsServer->ImportProductReviews($importRequest);

        if ($importResult->getStatus() === false) {
            $this->logger->error(\sprintf('Reviews import is failed: "%s"', $importResult->getMessage()));
        }
    }
}
