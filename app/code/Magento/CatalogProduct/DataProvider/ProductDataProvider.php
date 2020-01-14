<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider;

use Magento\CatalogProduct\Model\Storage\Client\Config\Product;
use Magento\CatalogProduct\Model\Storage\Client\QueryInterface;
use Magento\CatalogProduct\Model\Storage\State;
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
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $items = [];
        if (!$productIds) {
            return $items;
        }
        $products = [];
        $storageName = $this->storageState->getCurrentDataSourceName([$scopes['store'], Product::ENTITY_NAME]);
        try {
            $entities = $this->query->getEntries(
                $storageName,
                Product::ENTITY_NAME,
                $productIds,
                $this->getFirstLevelAttributes($attributes)
            );
        } catch (NotFoundException $notFoundException) {
            $this->logger->notice(
                \sprintf(
                    'Data Provider: Cannot find products for ids "%s" in the scope "%s"',
                    \implode(', ', $productIds),
                    \implode(', ', $scopes)
                )
            );
            return [];
        }
        foreach ($entities as $entry) {
            $data = $entry->getData();
            $products[$data['entity_id']] = $data;
        }

        return $this->prepareItemsOutput($products, $productIds);
    }

    /**
     * Get attributes of first level
     *
     * @param array $attributes
     * @return array
     */
    private function getFirstLevelAttributes($attributes): array
    {
        $firstLevel = ['entity_id'];
        foreach ($attributes as $name => $value) {
            $firstLevel[] = \is_array($value) ? $name : $value;
        }

        return $firstLevel;
    }

    /**
     * Process fetched data and prepare it for output format.
     *
     * @param array $items
     * @param int[] $productIds
     * @return array
     */
    private function prepareItemsOutput(array $items, array $productIds): array
    {
        // return items in the same order as product ids
        $sortedItems = [];
        foreach ($productIds as $id) {
            if (isset($items[$id])) {
                $sortedItems[$id] = $items[$id];
            }
        }

        return $sortedItems;
    }
}
