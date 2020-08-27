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
class FetchProducts implements FetchProductsInterface
{
    /**
     * Route to Export API products retrieval
     */
    private const EXPORT_API_GET_PRODUCTS = '/V1/catalog-export/products';

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
            $products = $this->restClient->get(
                self::EXPORT_API_GET_PRODUCTS,
                ['ids' => $ids, 'storeViewCodes' => $storeViewCodes],
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Cannot load products via "%s" with ids "%s"',
                    self::EXPORT_API_GET_PRODUCTS,
                    \implode(',', $ids)
                ),
                ['exception' => $e]
            );
            return [];
        }

        return $products;
    }
}
