<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogMessageBroker\HttpClient\RestClient;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class FetchCategories implements FetchCategoriesInterface
{
    /**
     * Route to Export API categories retrieval
     */
    private const EXPORT_API_GET_CATEGORIES = '/V1/catalog-export/categories';

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
    public function getByIds(array $ids, array $storeViewCodes = []): array
    {
        try {
            $categories = $this->restClient->get(
                self::EXPORT_API_GET_CATEGORIES,
                ['ids' => $ids, 'storeViewCodes' => $storeViewCodes],
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Cannot load categories via "%s" with ids "%s"',
                    self::EXPORT_API_GET_CATEGORIES,
                    \implode(',', $ids)
                ),
                ['exception' => $e]
            );
            return [];
        }

        return $categories;
    }
}
