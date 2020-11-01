<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsStorefront\Model;

use Magento\CatalogStorefront\Model\CatalogRepository;
use Magento\CatalogStorefrontApi\Api\Data\CustomerProductReviewRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\CustomerProductReviewResponse;
use Magento\CatalogStorefrontApi\Api\Data\CustomerProductReviewResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteReviewsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteReviewsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteReviewsResponseInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportReviewArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ImportReviewsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportReviewsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportReviewsResponseInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\PaginationResponse;
use Magento\CatalogStorefrontApi\Api\Data\ProductReviewCountRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductReviewCountResponse;
use Magento\CatalogStorefrontApi\Api\Data\ProductReviewCountResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductReviewRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductReviewResponse;
use Magento\CatalogStorefrontApi\Api\Data\ProductReviewResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ReadReviewMapper;
use Magento\CatalogStorefrontApi\Api\ProductReviewsServerInterface;
use Magento\ReviewsStorefront\DataProvider\ReviewDataProvider;
use Psr\Log\LoggerInterface;

/**
 * Class for retrieving & importing reviews data
 */
class ProductReviewsServer implements ProductReviewsServerInterface
{
    /**
     * @var ImportReviewArrayMapper
     */
    private $importReviewArrayMapper;

    /**
     * @var ImportReviewsResponseInterfaceFactory
     */
    private $importReviewsResponseInterfaceFactory;

    /**
     * @var DeleteReviewsResponseInterfaceFactory
     */
    private $deleteReviewsResponseInterfaceFactory;

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var ReviewDataProvider
     */
    private $reviewDataProvider;

    /**
     * @var ReadReviewMapper
     */
    private $readReviewMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ImportReviewArrayMapper $importReviewArrayMapper
     * @param ImportReviewsResponseInterfaceFactory $importReviewsResponseInterfaceFactory
     * @param DeleteReviewsResponseInterfaceFactory $deleteReviewsResponseInterfaceFactory
     * @param CatalogRepository $catalogRepository
     * @param ReviewDataProvider $reviewDataProvider
     * @param ReadReviewMapper $readReviewMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ImportReviewArrayMapper $importReviewArrayMapper,
        ImportReviewsResponseInterfaceFactory $importReviewsResponseInterfaceFactory,
        DeleteReviewsResponseInterfaceFactory $deleteReviewsResponseInterfaceFactory,
        CatalogRepository $catalogRepository,
        ReviewDataProvider $reviewDataProvider,
        ReadReviewMapper $readReviewMapper,
        LoggerInterface $logger
    ) {
        $this->importReviewArrayMapper = $importReviewArrayMapper;
        $this->importReviewsResponseInterfaceFactory = $importReviewsResponseInterfaceFactory;
        $this->deleteReviewsResponseInterfaceFactory = $deleteReviewsResponseInterfaceFactory;
        $this->catalogRepository = $catalogRepository;
        $this->reviewDataProvider = $reviewDataProvider;
        $this->readReviewMapper = $readReviewMapper;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function ImportProductReviews(ImportReviewsRequestInterface $request): ImportReviewsResponseInterface
    {
        $response = $this->importReviewsResponseInterfaceFactory->create();

        try {
            $reviewsInElasticFormat = [];

            foreach ($request->getReviews() as $review) {
                $review = $this->importReviewArrayMapper->convertToArray($review);
                $reviewsInElasticFormat['review'][$request->getStore()]['save'][] = $review;
            }

            $this->catalogRepository->saveToStorage($reviewsInElasticFormat);

            $response->setMessage('Records imported successfully');
            $response->setStatus(true);
        } catch (\Throwable $exception) {
            $response->setMessage($message = \sprintf('Cannot process reviews import: %s', $exception->getMessage()));
            $response->setStatus(false);
            $this->logger->error($message, ['exception' => $exception]);
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function DeleteProductReviews(DeleteReviewsRequestInterface $request): DeleteReviewsResponseInterface
    {
        $response = $this->deleteReviewsResponseInterfaceFactory->create();

        try {
            $reviewsInElasticFormat = [
                'review' => [
                    $request->getStore() => [
                        'delete' => $request->getReviewIds(),
                    ]
                ]
            ];

            $this->catalogRepository->saveToStorage($reviewsInElasticFormat);

            $response->setMessage('Reviews were removed successfully');
            $response->setStatus(true);
        } catch (\Throwable $exception) {
            $response->setMessage(
                $message = \sprintf('Cannot process reviews delete operation: %s', $exception->getMessage())
            );
            $response->setStatus(false);
            $this->logger->error($message, ['exception' => $exception]);
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function GetProductReviews(ProductReviewRequestInterface $request): ProductReviewResponseInterface
    {
        $items = [];
        $reviews = $this->reviewDataProvider->fetchByProductId(
            $request->getProductId(),
            $request->getStore(),
            $request->getPagination()
        );

        foreach ($reviews as $review) {
            $items[$review['id']] = $this->readReviewMapper->setData($review)->build();
        }

        $result = new ProductReviewResponse();
        $result->setItems($items);

        if (!empty($request->getPagination()) && !empty($items)) {
            $paginationResult = new PaginationResponse();
            $paginationResult->setPageSize(\count($items)); // Size of items returned
            $paginationResult->setCurrentPage(\array_key_last($items)); // current page = pointer = last id returned
            $result->setPagination($paginationResult);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function GetCustomerProductReviews(
        CustomerProductReviewRequestInterface $request
    ): CustomerProductReviewResponseInterface {
        $items = [];
        $reviews = $this->reviewDataProvider->fetchByCustomerId(
            $request->getCustomerId(),
            $request->getStore(),
            $request->getPagination()
        );

        foreach ($reviews as $review) {
            $items[] = $this->readReviewMapper->setData($review)->build();
        }

        $result = new CustomerProductReviewResponse();
        $result->setItems($items);

        if (!empty($request->getPagination()) && !empty($items)) {
            $paginationResult = new PaginationResponse();
            $paginationResult->setPageSize(\count($items)); // Size of items returned
            $paginationResult->setCurrentPage(\array_key_last($items)); // current page = pointer = last id returned
            $result->setPagination($paginationResult);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function GetProductReviewCount(
        ProductReviewCountRequestInterface $request
    ): ProductReviewCountResponseInterface {
        $reviewCount = $this->reviewDataProvider->getReviewsCount($request->getProductId(), $request->getStore());

        $result = new ProductReviewCountResponse();
        $result->setReviewCount($reviewCount);

        return $result;
    }
}
