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
    public function execute(array $entities, string $scope): array
    {
        try {
            $products = $this->restClient->get(
                self::EXPORT_API_GET_PRODUCTS,
                $this->prepareRequestData($entities, $scope)
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Cannot load products via "%s" with ids "%s" for scope "%s"',
                    self::EXPORT_API_GET_PRODUCTS,
                    \implode(',', \array_map(function (Entity $entity) {
                        return $entity->getEntityId();
                    }, $entities)),
                    $scope
                ),
                ['exception' => $e]
            );
            return [];
        }

        return $products;
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
        $products = [];

        foreach ($entities as $entity) {
            $products[] = [
                'entity_id' => $entity->getEntityId(),
                'attribute_codes' => $entity->getAttributes()
            ];
        }

        return [
            'request' => [
                'entities' => $products,
                'storeViewCodes' => [$storeCode],
            ],
        ];
    }
}
