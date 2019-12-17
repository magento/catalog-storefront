<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogBundleProduct\DataProvider;

use Magento\CatalogProduct\DataProvider\NestedDataProviderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogBundleProduct\DataProvider\Query\Items\BundleProductItemOptionsBuilder as QueryBuilder;

/**
 * @inheritdoc
 */
class BundleProductItemOptions implements NestedDataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var array
     */
    private $priceTypes = [
        'FIXED',
        'PERCENT',
        'DYNAMIC',
    ];

    /**
     * @param ResourceConnection $resourceConnection
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QueryBuilder $queryBuilder
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @inheritdoc
     *
     * @param string[] $attributes
     * @param string[] $scopes
     * @param array[][] $parentData
     * @return array
     * @throws \Exception
     */
    public function fetch(array $attributes, array $scopes, array $parentData): array
    {
        $attributes = $attributes['options'] ?? [];

        $optionIds = $this->collectFieldIds($parentData, 'option_id');
        $parentIds = $this->collectFieldIds($parentData, 'parent_id');
        $optionParents = $this->getOptionParentIds($parentData);

        $bundleItemOptionsSelect = $this->queryBuilder->build(
            $optionIds,
            $parentIds,
            $attributes
        );

        $connection = $this->resourceConnection->getConnection();
        $options = $connection->fetchAll($bundleItemOptionsSelect);
        if (empty($options)) {
            return $parentData;
        }
        $options = $this->prepareOptions($options);
        $optionsByEntityId = $this->indexByField($options, $optionParents);

        foreach ($parentData as $entityId => $entityData) {
            foreach ($entityData['items'] as $key => $item) {
                $optionId = $item['option_id'];
                if (!isset($optionsByEntityId[$optionId])) {
                    continue;
                }
                $parentData[$entityId]['items'][$key]['options'] = $optionsByEntityId[$optionId];
            }
        }

        return $parentData;
    }

    /**
     * Iterate an array and collect data for given $field
     *
     * @param array $data
     * @param string $field
     * @return array
     */
    private function collectFieldIds(array $data, string $field): array
    {
        $fieldIds = [];

        foreach ($data as $row) {
            foreach ($row['items'] as $item) {
                if (!isset($item[$field])) {
                    continue;
                }
                $fieldIds []= $item[$field];
            }
        }

        return $fieldIds;
    }

    /**
     * Retrieve parent ids for options.
     *
     * $optionsParents = [
     *   'option1' => [
     *      1,
     *      2,
     *    ],
     *   'option2' => [
     *      3,
     *      4,
     *    ],
     * ];
     *
     * @param array $data
     * @return array
     */
    private function getOptionParentIds(array $data): array
    {
        $optionParents = [];

        foreach ($data as $row) {
            foreach ($row['items'] as $item) {
                $optionParents[$item['option_id']][] = $item['parent_id'];
            }
        }

        return $optionParents;
    }

    /**
     * Index array by field.
     *
     * @param array $items
     * @param array $optionParents
     * @return array[][]
     */
    private function indexByField(array $items, array $optionParents): array
    {
        $result = [];

        foreach ($items as $item) {
            $optionId = $item['option_id'];
            $parentId = $item['parent_id'];
            if (!\in_array($parentId, $optionParents[$optionId], true)) {
                continue;
            }
            $result[$optionId][] = $item;
        }

        return $result;
    }

    /**
     * Prepare options for output.
     *
     * @param array[] $options
     * @return array
     */
    private function prepareOptions(array $options): array
    {
        foreach ($options as $key => $option) {
            if (isset($option['price_type'])) {
                $options[$key]['price_type'] = $this->priceTypes[$option['price_type']] ?? 'DYNAMIC';
            }
        }

        return $options;
    }
}
