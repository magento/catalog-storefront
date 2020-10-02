<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\DataProvider;

use Magento\CatalogStorefront\Model\Storage\Client\Config\Product;
use Magento\CatalogStorefront\Model\Storage\Client\QueryInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Product storage reader.
 */
class ProductDataProvider
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
     * Fetch product data from storage
     *
     * @param array $productIds
     * @param array $attributes
     * @param array $scopes
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @throws \Throwable
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $items = [];
        if (!$productIds) {
            return $items;
        }
        $storageName = $this->storageState->getCurrentDataSourceName([$scopes['store'], Product::ENTITY_NAME]);
        try {
            $entities = $this->query->getEntries(
                $storageName,
                Product::ENTITY_NAME,
                $productIds,
                $this->getFirstLevelAttributes($attributes)
            );
        } catch (NotFoundException $notFoundException) {
            $this->logger->error(
                \sprintf(
                    'Cannot find products for ids "%s" in the scope "%s"',
                    \implode(', ', $productIds),
                    \implode(', ', \array_keys($scopes))  . ':' . \implode(', ', $scopes)
                ),
                ['exception' => $notFoundException]
            );
            return [];
        } catch (\Throwable $e) {
            $this->logger->error($e);
            throw $e;
        }

        $products = $entities->toArray();
        // TODO: MC-31164 ad-hoc fix to handle issue with mapping on configurable product creation in elasticsearch
        foreach ($products as &$product) {
            if (isset($product['variants']) && is_string($product['variants'])) {
                $product['variants'] = \json_decode($product['variants'], true);
            }
        }

        return $products;
    }

    /**
     * Get attributes of first level
     *
     * @param array $attributes
     * @return array
     */
    private function getFirstLevelAttributes($attributes): array
    {
        $firstLevel = ['entity_id', 'sku', 'type_id'];
        foreach ($attributes as $name => $value) {
            if (\is_array($value)) {
                $firstLevel[] = \strpos($name, '.') !== false ? \explode('.', $name)[1] : $name;
            } else {
                $firstLevel[] = $value;
            }
        }

        return $firstLevel;
    }
}
