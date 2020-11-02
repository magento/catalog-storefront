<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsStorefront\Model;

use Magento\CatalogStorefront\Model\CatalogRepository;
use Magento\CatalogStorefrontApi\Api\Data\DeleteRatingsMetadataRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteRatingsMetadataResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteRatingsMetadataResponseInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportRatingsMetadataRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportRatingsMetadataResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportRatingsMetadataResponseInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\RatingMetadataArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\RatingMetadataMapper;
use Magento\CatalogStorefrontApi\Api\Data\RatingsMetadataRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\RatingsMetadataResponse;
use Magento\CatalogStorefrontApi\Api\Data\RatingsMetadataResponseInterface;
use Magento\CatalogStorefrontApi\Api\RatingsMetadataServerInterface;
use Magento\ReviewsStorefront\DataProvider\RatingMetadataProvider;
use Psr\Log\LoggerInterface;

/**
 * Class for retrieving & importing rating metadata
 */
class RatingsMetadataServer implements RatingsMetadataServerInterface
{
    /**
     * @var RatingMetadataArrayMapper
     */
    private $ratingMetadataArrayMapper;

    /**
     * @var ImportRatingsMetadataResponseInterfaceFactory
     */
    private $importRatingsMetadataResponseInterfaceFactory;

    /**
     * @var DeleteRatingsMetadataResponseInterfaceFactory
     */
    private $deleteRatingsMetadataResponseInterfaceFactory;

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var RatingMetadataProvider
     */
    private $ratingMetadataProvider;

    /**
     * @var RatingMetadataMapper
     */
    private $ratingMetadataMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RatingMetadataArrayMapper $ratingMetadataArrayMapper
     * @param ImportRatingsMetadataResponseInterfaceFactory $importRatingsMetadataResponseInterfaceFactory
     * @param DeleteRatingsMetadataResponseInterfaceFactory $deleteRatingsMetadataResponseInterfaceFactory
     * @param CatalogRepository $catalogRepository
     * @param RatingMetadataProvider $ratingMetadataProvider
     * @param RatingMetadataMapper $ratingMetadataMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        RatingMetadataArrayMapper $ratingMetadataArrayMapper,
        ImportRatingsMetadataResponseInterfaceFactory $importRatingsMetadataResponseInterfaceFactory,
        DeleteRatingsMetadataResponseInterfaceFactory $deleteRatingsMetadataResponseInterfaceFactory,
        CatalogRepository $catalogRepository,
        RatingMetadataProvider $ratingMetadataProvider,
        RatingMetadataMapper $ratingMetadataMapper,
        LoggerInterface $logger
    ) {
        $this->ratingMetadataArrayMapper = $ratingMetadataArrayMapper;
        $this->importRatingsMetadataResponseInterfaceFactory = $importRatingsMetadataResponseInterfaceFactory;
        $this->deleteRatingsMetadataResponseInterfaceFactory = $deleteRatingsMetadataResponseInterfaceFactory;
        $this->catalogRepository = $catalogRepository;
        $this->ratingMetadataProvider = $ratingMetadataProvider;
        $this->ratingMetadataMapper = $ratingMetadataMapper;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function ImportRatingsMetadata(
        ImportRatingsMetadataRequestInterface $request
    ): ImportRatingsMetadataResponseInterface {
        $response = $this->importRatingsMetadataResponseInterfaceFactory->create();

        try {
            $ratingsMetadataInElasticFormat = [];
            $storeCode = $request->getStore();

            foreach ($request->getMetadata() as $metadata) {
                $ratingMetadata = $this->ratingMetadataArrayMapper->convertToArray($metadata);
                $ratingsMetadataInElasticFormat['rating_metadata'][$storeCode]['save'][] = $ratingMetadata;
            }

            $this->catalogRepository->saveToStorage($ratingsMetadataInElasticFormat);

            $response->setMessage('Records imported successfully');
            $response->setStatus(true);
        } catch (\Throwable $exception) {
            $response->setMessage(
                $message = \sprintf('Cannot process rating metadata import: %s', $exception->getMessage())
            );
            $response->setStatus(false);
            $this->logger->error($message, ['exception' => $exception]);
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function DeleteRatingsMetadata(
        DeleteRatingsMetadataRequestInterface $request
    ): DeleteRatingsMetadataResponseInterface {
        $response = $this->deleteRatingsMetadataResponseInterfaceFactory->create();

        try {
            $ratingsMetadataInElasticFormat = [
                'rating_metadata' => [
                    $request->getStore() => [
                        'delete' => $request->getRatingIds(),
                    ]
                ]
            ];

            $this->catalogRepository->saveToStorage($ratingsMetadataInElasticFormat);

            $response->setMessage('Ratings metadata was removed successfully');
            $response->setStatus(true);
        } catch (\Throwable $exception) {
            $response->setMessage(
                $message = \sprintf('Cannot process rating metadata delete operation: %s', $exception->getMessage())
            );
            $response->setStatus(false);
            $this->logger->error($message, ['exception' => $exception]);
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function GetRatingsMetadata(
        RatingsMetadataRequestInterface $request
    ): RatingsMetadataResponseInterface {
        $items = [];
        $metadata = $this->ratingMetadataProvider->fetch($request->getRatingIds(), $request->getStore());

        foreach ($metadata as $data) {
            $items[] = $this->ratingMetadataMapper->setData($data)->build();
        }

        $result = new RatingsMetadataResponse();
        $result->setItems($items);

        return $result;
    }
}
