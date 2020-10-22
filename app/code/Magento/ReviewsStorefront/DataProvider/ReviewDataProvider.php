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
use Magento\ReviewsStorefront\Model\Storage\Client\Config\Review;
use Psr\Log\LoggerInterface;

/**
 * Review storage reader.
 */
class ReviewDataProvider
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
     * Fetch reviews by product id and scope code
     *
     * @param int $productId
     * @param string $scope
     *
     * @return array
     *
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function fetchByProductId(int $productId, string $scope): array
    {
        $storageName = $this->storageState->getCurrentDataSourceName([Review::ENTITY_NAME]);

        try {
            $entities = $this->query->searchEntries(
                $storageName,
                Review::ENTITY_NAME,
                ['product_id' => $productId, 'visibility' => $scope]
            );
        } catch (NotFoundException $notFoundException) {
            $this->logger->error(
                \sprintf('Cannot find reviews for product id "%s" in the scope "%s"', $productId, $scope),
                ['exception' => $notFoundException]
            );

            return [];
        } catch (\Throwable $e) {
            $this->logger->error($e);
            throw $e;
        }

        return $entities->toArray();
    }

    /**
     * Fetch reviews by customer id and scope code
     *
     * @param int $customerId
     * @param string $scope
     *
     * @return array
     *
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function fetchByCustomerId(int $customerId, string $scope): array
    {
        $storageName = $this->storageState->getCurrentDataSourceName([Review::ENTITY_NAME]);

        try {
            $entities = $this->query->searchEntries(
                $storageName,
                Review::ENTITY_NAME,
                ['customer_id' => $customerId, 'visibility' => $scope]
            );
        } catch (NotFoundException $notFoundException) {
            $this->logger->error(
                \sprintf('Cannot find reviews for customer id "%s" in the scope "%s"', $customerId, $scope),
                ['exception' => $notFoundException]
            );

            return [];
        } catch (\Throwable $e) {
            $this->logger->error($e);
            throw $e;
        }

        return $entities->toArray();
    }

    /**
     * @param int $productId
     * @param string $scope
     *
     * @return int
     *
     * @throws \Throwable
     */
    public function getReviewsCount(int $productId, string $scope): int
    {
        $storageName = $this->storageState->getCurrentDataSourceName([Review::ENTITY_NAME]);

        try {
            $reviewsCount = $this->query->getEntriesCount(
                $storageName,
                Review::ENTITY_NAME,
                ['product_id' => $productId, 'visibility' => $scope]
            );
        } catch (\Throwable $e) {
            $this->logger->error($e);
            throw $e;
        }

        return $reviewsCount;
    }
}
