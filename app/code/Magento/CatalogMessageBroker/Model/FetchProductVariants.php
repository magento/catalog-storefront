<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogMessageBroker\HttpClient\RestClient;
use Magento\CatalogExport\Event\Data\Entity;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class FetchProductVariants implements FetchProductVariantsInterface
{
    /**
     * Route to Export API product variants retrieval
     */
    private const EXPORT_API_GET_VARIANTS = '/V1/catalog-export/product-variants';

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
        $data = $this->prepareRequestData($entities);
        try {
            $variants = $this->restClient->get(
                self::EXPORT_API_GET_VARIANTS,
                $data
            );

        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Cannot load product variants via "%s" with ids "%s"',
                    self::EXPORT_API_GET_VARIANTS,
                    \implode(',', \array_map(function (Entity $entity) {
                        return $entity->getEntityId();
                    }, $entities))
                ),
                ['exception' => $e]
            );
            return [];
        }

        return $variants;
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
        $variants = [];
        foreach ($entities as $entity) {
            $variants['ids'][] = $entity->getEntityId();
        }
        return $variants;
    }
}
