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
class FetchRatingsMetadata implements FetchRatingsMetadataInterface
{
    /**
     * Route to Export API ratings metadata retrieval
     */
    private const EXPORT_API_GET_RATINGS_METADATA = '/V1/reviews-export/ratings-metadata';

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
    public function execute(array $entities, string $scope): array
    {
        try {
            $reviews = $this->restClient->get(
                self::EXPORT_API_GET_RATINGS_METADATA,
                $this->prepareRequestData($entities, $scope)
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Cannot load ratings metadata via "%s" with ids "%s" for scope "%s"',
                    self::EXPORT_API_GET_RATINGS_METADATA,
                    \implode(',', \array_map(function (Entity $entity) {
                        return $entity->getEntityId();
                    }, $entities)),
                    $scope
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
     * @param string $storeCode
     *
     * @return array
     */
    private function prepareRequestData(array $entities, string $storeCode): array
    {
        $ratings = [];

        foreach ($entities as $entity) {
            $ratings[] = [
                'entity_id' => $entity->getEntityId(),
            ];
        }

        return [
            'request' => [
                'entities' => $ratings,
                'storeViewCodes' => [$storeCode],
            ],
        ];
    }
}
