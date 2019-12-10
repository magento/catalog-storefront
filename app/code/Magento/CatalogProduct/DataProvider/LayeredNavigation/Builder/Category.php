<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\LayeredNavigation\Builder;

use Magento\CatalogProduct\DataProvider\Query\Category\CategoryAttributeQueryBuilder;
use Magento\CatalogProduct\DataProvider\LayeredNavigation\LayerBuilderInterface;
use Magento\CatalogProduct\DataProvider\LayeredNavigation\RootCategoryProvider;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\AggregationValueInterface;
use Magento\Framework\Api\Search\BucketInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogProduct\DataProvider\Query\AttributesDataConverter;

/**
 * @inheritdoc
 */
class Category implements LayerBuilderInterface
{
    /**
     * @var string
     */
    private const CATEGORY_BUCKET = 'category_bucket';

    /**
     * @var array
     */
    private static $bucketMap = [
        self::CATEGORY_BUCKET => [
            'request_name' => 'category_id',
            'label' => 'Category'
        ],
    ];

    /**
     * @var CategoryAttributeQueryBuilder
     */
    private $categoryAttributeQueryBuilder;

    /**
     * @var AttributesDataConverter
     */
    private $attributesDataConverter;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var RootCategoryProvider
     */
    private $rootCategoryProvider;

    /**
     * @param CategoryAttributeQueryBuilder $categoryAttributeQueryBuilder
     * @param AttributesDataConverter $attributesDataConverter
     * @param RootCategoryProvider $rootCategoryProvider
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        CategoryAttributeQueryBuilder $categoryAttributeQueryBuilder,
        AttributesDataConverter $attributesDataConverter,
        RootCategoryProvider $rootCategoryProvider,
        ResourceConnection $resourceConnection
    ) {
        $this->categoryAttributeQueryBuilder = $categoryAttributeQueryBuilder;
        $this->attributesDataConverter = $attributesDataConverter;
        $this->resourceConnection = $resourceConnection;
        $this->rootCategoryProvider = $rootCategoryProvider;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $bucket = $aggregation->getBucket(self::CATEGORY_BUCKET);
        if ($this->isBucketEmpty($bucket)) {
            return [];
        }
        
        $categoryIds =  \array_map(
            function (AggregationValueInterface $value) {
                return (int)$value->getValue();
            },
            $bucket->getValues()
        );

        $categoryIds = \array_diff($categoryIds, [$this->rootCategoryProvider->getRootCategory($storeId)]);
        $categoryLabels = \array_column(
            $this->attributesDataConverter->convert(
                $this->resourceConnection->getConnection()->fetchAll(
                    $this->categoryAttributeQueryBuilder->build($categoryIds, ['name'], $storeId)
                )
            ),
            'name',
            'entity_id'
        );

        if (!$categoryLabels) {
            return [];
        }

        $result = $this->buildLayer(
            self::$bucketMap[self::CATEGORY_BUCKET]['label'],
            \count($categoryIds),
            self::$bucketMap[self::CATEGORY_BUCKET]['request_name']
        );

        foreach ($bucket->getValues() as $value) {
            $categoryId = $value->getValue();
            if (!\in_array($categoryId, $categoryIds, true)) {
                continue ;
            }
            $result['options'][] = $this->buildItem(
                $categoryLabels[$categoryId] ?? $categoryId,
                $categoryId,
                $value->getMetrics()['count']
            );
        }

        return [$result];
    }

    /**
     * Format layer data
     *
     * @param string $layerName
     * @param string $itemsCount
     * @param string $requestName
     * @return array
     */
    private function buildLayer($layerName, $itemsCount, $requestName): array
    {
        return [
            'label' => $layerName,
            'count' => $itemsCount,
            'attribute_code' => $requestName
        ];
    }
    
    /**
     * Format layer item data
     *
     * @param string $label
     * @param string|int $value
     * @param string|int $count
     * @return array
     */
    private function buildItem($label, $value, $count): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'count' => $count,
        ];
    }

    /**
     * Check that bucket contains data
     *
     * @param BucketInterface|null $bucket
     * @return bool
     */
    private function isBucketEmpty(?BucketInterface $bucket): bool
    {
        return null === $bucket || !$bucket->getValues();
    }
}
