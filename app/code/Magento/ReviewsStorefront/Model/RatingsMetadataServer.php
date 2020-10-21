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
use Magento\CatalogStorefrontApi\Api\Data\RatingsMetadataRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\RatingsMetadataResponseInterface;
use Magento\CatalogStorefrontApi\Api\RatingsMetadataServerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RatingMetadataArrayMapper $ratingMetadataArrayMapper
     * @param ImportRatingsMetadataResponseInterfaceFactory $importRatingsMetadataResponseInterfaceFactory
     * @param DeleteRatingsMetadataResponseInterfaceFactory $deleteRatingsMetadataResponseInterfaceFactory
     * @param CatalogRepository $catalogRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        RatingMetadataArrayMapper $ratingMetadataArrayMapper,
        ImportRatingsMetadataResponseInterfaceFactory $importRatingsMetadataResponseInterfaceFactory,
        DeleteRatingsMetadataResponseInterfaceFactory $deleteRatingsMetadataResponseInterfaceFactory,
        CatalogRepository $catalogRepository,
        LoggerInterface $logger
    ) {
        $this->ratingMetadataArrayMapper = $ratingMetadataArrayMapper;
        $this->importRatingsMetadataResponseInterfaceFactory = $importRatingsMetadataResponseInterfaceFactory;
        $this->deleteRatingsMetadataResponseInterfaceFactory = $deleteRatingsMetadataResponseInterfaceFactory;
        $this->catalogRepository = $catalogRepository;
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
                $ratingMetadata['id'] = \base64_decode($ratingMetadata['rating_id']);
                $ratingMetadata['store_code'] = $storeCode;
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
                        'delete' => \array_map('base64_decode', $request->getRatingIds()),
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
        // TODO: Implement GetRatingsMetadata() method.
    }
}
