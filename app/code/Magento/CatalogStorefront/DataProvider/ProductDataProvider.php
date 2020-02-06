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
     * @var LinkedEntityHydrator
     */
    private $linkedEntityHydrator;

    /**
     * @param QueryInterface $query
     * @param State $storageState
     * @param LoggerInterface $logger
     * @param LinkedEntityHydrator $linkedEntityHydrator
     */
    public function __construct(
        QueryInterface $query,
        State $storageState,
        LoggerInterface $logger,
        LinkedEntityHydrator $linkedEntityHydrator
    ) {
        $this->query = $query;
        $this->storageState = $storageState;
        $this->logger = $logger;
        $this->linkedEntityHydrator = $linkedEntityHydrator;
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
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $items = [];
        if (!$productIds) {
            return $items;
        }
        $products = [];
        $storageName = $this->storageState->getCurrentDataSourceName([$scopes['store'], Product::ENTITY_NAME]);
        $entities = [];
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
                    \implode(', ', $scopes)
                ),
                ['exception' => $notFoundException]
            );
            return [];
        } catch (\Throwable $e) {
            $this->logger->error($e);
        }

        foreach ($entities as $entry) {
            $data = $entry->getData();
            if (!$data) {
                continue;
            }
            $data['id'] = $entry->getId();
            $products[$entry->getId()] = $data;
        }

        $products = $this->linkedEntityHydrator->hydrate($products, $attributes, $scopes);

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
