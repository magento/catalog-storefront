<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsMessageBroker\Model;

use Magento\CatalogMessageBroker\HttpClient\RestClient;
use Magento\CatalogExport\Event\Data\Entity;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class FetchReviews implements FetchReviewsInterface
{
    /**
     * Route to Export API reviews retrieval
     */
    private const EXPORT_API_GET_REVIEWS = '/V1/reviews-export/reviews';

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RestClient $restClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        RestClient $restClient,
        LoggerInterface $logger
    ) {
        $this->restClient = $restClient;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities): array
    {
        try {
            $reviews = $this->restClient->get(
                self::EXPORT_API_GET_REVIEWS,
                $this->prepareRequestData($entities)
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Cannot load reviews via "%s" with ids "%s"',
                    self::EXPORT_API_GET_REVIEWS,
                    \implode(',', \array_map(function (Entity $entity) {
                        return $entity->getEntityId();
                    }, $entities))
                ),
                ['exception' => $e]
            );
            return [];
        }

        return $reviews;
    }

    /**
     * Prepare client request data
     *
     * @param Entity[] $entities
     *
     * @return array
     */
    private function prepareRequestData(array $entities): array
    {
        $reviews = [];

        foreach ($entities as $entity) {
            $reviews[] = [
                'entity_id' => $entity->getEntityId(),
            ];
        }

        return [
            'request' => [
                'entities' => $reviews,
            ],
        ];
    }
}
