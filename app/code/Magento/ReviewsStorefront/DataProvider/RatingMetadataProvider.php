<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsStorefront\DataProvider;

use Magento\CatalogStorefront\Model\Storage\Client\QueryInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;
use Magento\ReviewsStorefront\Model\Storage\Client\Config\RatingMetadata;
use Psr\Log\LoggerInterface;

/**
 * Rating metadata storage reader.
 */
class RatingMetadataProvider
{
    /**
     * @var QueryInterface
     */
    private $query;

    /**
     * @var State
     */
    private $storageState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param QueryInterface $query
     * @param State $storageState
     * @param LoggerInterface $logger
     */
    public function __construct(
        QueryInterface $query,
        State $storageState,
        LoggerInterface $logger
    ) {
        $this->query = $query;
        $this->storageState = $storageState;
        $this->logger = $logger;
    }

    /**
     * Fetch rating metadata
     *
     * @param string[] $ratingIds
     * @param string $scope
     *
     * @return array
     *
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function fetch(array $ratingIds, string $scope): array
    {
        $storageName = $this->storageState->getCurrentDataSourceName([$scope, RatingMetadata::ENTITY_NAME]);

        try {
            $entities = $this->query->getEntries($storageName, RatingMetadata::ENTITY_NAME, $ratingIds, []);
        } catch (NotFoundException $notFoundException) {
            $this->logger->error(
                \sprintf(
                    'Cannot find rating metadata for ids "%s" in the scope "%s"',
                    \implode(', ', $ratingIds),
                    $scope
                ),
                ['exception' => $notFoundException]
            );

            return [];
        } catch (\Throwable $e) {
            $this->logger->error($e);
            throw $e;
        }

        return $entities->toArray();
    }
}
