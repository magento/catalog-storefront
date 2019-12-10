<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCategory\DataProvider;

use Magento\Framework\ObjectManagerInterface;

/**
 * @inheritdoc
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var array
     */
    private $dataProviders;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $defaultDataProvider;

    /**
     * @param string $defaultDataProvider
     * @param ObjectManagerInterface $objectManager
     * @param array $dataProviders
     */
    public function __construct(
        string $defaultDataProvider,
        ObjectManagerInterface $objectManager,
        array $dataProviders = []
    ) {
        $this->defaultDataProvider = $defaultDataProvider;
        $this->dataProviders = $dataProviders;
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    public function fetch(array $categoryIds, array $attributes, array $scopes): array
    {
        $items = [];
        if (!$categoryIds) {
            return $items;
        }

        $dataProviders = $this->getDataProviders($attributes);

        foreach ($dataProviders as $dataProviderClass => $dataAttributes) {
            $dataProvider = $this->objectManager->get($dataProviderClass);
            if (!$dataProvider instanceof DataProviderInterface) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Data provider "%s" must implement %s',
                        $dataProviderClass,
                        DataProviderInterface::class
                    )
                );
            }
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $mergedAttributes = \array_merge(...$dataAttributes);
            $items[] = $dataProvider->fetch($categoryIds, $mergedAttributes, $scopes);
        }

        return $this->prepareItemsOutput($items, $categoryIds);
    }

    /**
     * Process fetched data and prepare it for output format.
     *
     * @param array $items
     * @param int[] $categoryIds
     * @return array
     */
    private function prepareItemsOutput(array $items, array $categoryIds): array
    {
        if ($items) {
            $items = \array_replace_recursive(...$items);
        } else {
            $items = \array_combine($categoryIds, \array_fill(0, \count($categoryIds), []));
        }

        // return items in the same order as category ids
        $sortedItems = [];
        foreach ($categoryIds as $id) {
            if (isset($items[$id])) {
                $sortedItems[$id] = $items[$id];
            }
        }

        return $sortedItems;
    }

    /**
     * Get data providers for specified attributes
     *
     * @param array $attributes
     *
     * @return array
     */
    private function getDataProviders(array $attributes): array
    {
        $attributesProviderMap = [];
        foreach ($attributes as $attributeName => $attributeOutput) {
            if (false === \is_string($attributeName) && \is_string($attributeOutput)) {
                $attributeName = $attributeOutput;
                $attributeOutput = [$attributeOutput];
            }
            if (isset($this->dataProviders[$attributeName])) {
                $attributesProviderMap[$this->dataProviders[$attributeName]][] = [$attributeName => $attributeOutput];
            } else {
                // Default attributes provider do not support nested attributes
                $attributesProviderMap[$this->defaultDataProvider][] = [$attributeName => $attributeOutput];
            }
        }

        return $attributesProviderMap;
    }
}
