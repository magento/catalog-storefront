<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * General data provider that returns EAV attributes for eav entities.
 *
 * Attributes that comes to API may have dot-notation with product type prefix (e.g. BundleProduct.item) to give ability
 * to select only needed attributes for specific product type.
 * Returned list of attributes do not contains product type prefix, but this prefix used to select attributes
 * for specific data providers.
 * Attribute, that passed to Data Provider do not contain product type prefix.
 *
 * @inheritdoc
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var array
     */
    private $dataProviders;

    /**
     * @var string
     */
    private $defaultDataProvider;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Transformer
     */
    private $transformer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $productTypesMap;

    /**
     * @param string $defaultDataProvider
     * @param ObjectManagerInterface $objectManager
     * @param Transformer $transformer
     * @param StoreManagerInterface $storeManager
     * @param array $dataProviders
     * @param array $productTypesMap
     */
    public function __construct(
        string $defaultDataProvider,
        ObjectManagerInterface $objectManager,
        Transformer $transformer,
        StoreManagerInterface $storeManager,
        array $dataProviders = [],
        array $productTypesMap = []
    ) {
        $this->dataProviders = $dataProviders;
        $this->defaultDataProvider = $defaultDataProvider;
        $this->objectManager = $objectManager;
        $this->transformer = $transformer;
        $this->storeManager = $storeManager;
        $this->productTypesMap = $productTypesMap;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     * @throws NoSuchEntityException
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $items = [];
        if (!$productIds) {
            return $items;
        }

        if (!empty($scopes['store'])) {
            $scopes['store'] = $this->storeManager->getStore($scopes['store'])->getId();
        }

        $dataProviders = $this->getDataProviders($attributes);
        $generalDataProviderResult = $this->getGeneralDataProviderResults($productIds, $dataProviders, $scopes);

        if (!$generalDataProviderResult) {
            return $items;
        }
        unset($dataProviders[$this->defaultDataProvider]);

        $entityTypeProductIds = [];
        $productIds = \array_combine($productIds, $productIds);

        $existingProductIds = \array_keys($generalDataProviderResult);
        $productIds = \array_intersect($productIds, $existingProductIds);
        foreach ($generalDataProviderResult as $entityId => $entityData) {
            if (!isset($entityData['type_id'])) {
                unset($productIds[$entityId]);
                continue;
            }
            $entityTypeProductIds[$entityData['type_id']][] = $entityId;
        }
        $items[] = $generalDataProviderResult;

        foreach ($dataProviders as $dataProviderClass => $dataAttributes) {
            $providerAlias = \array_flip($this->dataProviders)[$dataProviderClass];

            // check if we have products of specific product type, otherwise skip processing by data provider
            $productType = $this->productTypesMap[$providerAlias] ?? null;
            $productIdsPerType = $productType
                ? ($entityTypeProductIds[$productType] ?? [])
                : $productIds;
            if (!$productIdsPerType) {
                continue;
            }

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

            $items[] = $dataProvider->fetch($productIdsPerType, $mergedAttributes, $scopes);
        }

        return $this->prepareItemsOutput($items, $attributes, $productIds);
    }

    /**
     * Process fetched data and prepare it for output format.
     *
     * @param array $items
     * @param array $attributes
     * @param int[] $productIds
     * @return array
     */
    private function prepareItemsOutput(array $items, array $attributes, array $productIds): array
    {
        if ($items) {
            $items = \array_replace_recursive(...$items);
            $items = $this->transformer->transform($items, $attributes);
        } else {
            $items = \array_combine($productIds, \array_fill(0, \count($productIds), []));
        }

        return $items;
    }

    /**
     * Fetch eav attributes for given products.
     *
     * @param int[] $productIds
     * @param array $dataProviders
     * @param array $scopes
     * @return mixed
     */
    private function getGeneralDataProviderResults(array $productIds, array $dataProviders, array $scopes)
    {
        $generalDataProvider = $this->objectManager->get($this->defaultDataProvider);
        $mergedAttributes = isset($dataProviders[$this->defaultDataProvider])
            ? \array_merge(...$dataProviders[$this->defaultDataProvider])
            : [];

        return $generalDataProvider->fetch($productIds, $mergedAttributes, $scopes);
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

        if (empty($attributes)) {
            $attributesProviderMap[$this->defaultDataProvider] = [[]];
            foreach ($this->dataProviders as $attributeName => $dataProvider) {
                $attributesProviderMap[$dataProvider][] = [];
            }

            return $attributesProviderMap;
        }

        foreach ($attributes as $attributeName => $outputAttributes) {
            $cleanAttributeName = $attributeName;
            if (\is_string($attributeName) && false !== \strpos($attributeName, '.')) {
                $cleanAttributeName = \substr($attributeName, \strpos($attributeName, '.') + 1);
            }

            if (\is_string($outputAttributes)) {
                $cleanAttributeName = $attributeName = $outputAttributes;
                if (false !== \strpos($outputAttributes, '.')) {
                    $cleanAttributeName = \substr($outputAttributes, \strpos($outputAttributes, '.') + 1);
                }
                $outputAttributes = [$outputAttributes];
            }

            if (isset($this->dataProviders[$attributeName])) {
                $attributesProviderMap[$this->dataProviders[$attributeName]][] = [
                    $cleanAttributeName => $outputAttributes
                ];
            } else {
                // Default attributes provider do not support nested attributes
                $attributesProviderMap[$this->defaultDataProvider][] = [$cleanAttributeName];
            }
        }

        return $attributesProviderMap;
    }
}
