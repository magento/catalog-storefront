<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDownloadableProduct\DataProvider;

use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\UrlInterface;
use Magento\CatalogDownloadableProduct\DataProvider\Query\DownloadableItemsBuilderInterface;

/**
 * @inheritdoc
 */
class DownloadableProductItems implements DataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DownloadableItemsBuilderInterface
     */
    private $downloadableItemsBuilder;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $attributeName;

    /**
     * @var string
     */
    private $routePath;

    /**
     * @var string
     */
    private $tableKey;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DownloadableItemsBuilderInterface $downloadableItemsBuilder
     * @param UrlInterface $urlBuilder
     * @param string $attributeName
     * @param string $routePath
     * @param string $tableKey
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DownloadableItemsBuilderInterface $downloadableItemsBuilder,
        UrlInterface $urlBuilder,
        string $attributeName,
        string $routePath,
        string $tableKey
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->downloadableItemsBuilder = $downloadableItemsBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->attributeName = $attributeName;
        $this->routePath = $routePath;
        $this->tableKey = $tableKey;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $storeId = (int)$scopes['store'];

        $connection = $this->resourceConnection->getConnection();

        $attributes = $attributes[$this->attributeName] ?? [];
        $downloadableProductItems = $this->downloadableItemsBuilder->build(
            $productIds,
            $attributes,
            $storeId
        );

        $items = $connection->fetchAll($downloadableProductItems);
        if (empty($items)) {
            return [];
        }
        $items = $this->addSampleUrl($items);
        $itemsByEntityId = $this->indexByField($items, 'entity_id');

        $result = [];
        foreach ($itemsByEntityId as $entityId => $records) {
            $result[$entityId] = [
                $this->attributeName => $records,
            ];
        }

        return $result;
    }

    /**
     * Index array by field.
     *
     * @param array  $items
     * @param string $field
     * @return array[][]
     */
    private function indexByField(array $items, string $field): array
    {
        $result = [];

        foreach ($items as $item) {
            $result[$item[$field]][] = $item;
        }

        return $result;
    }

    /**
     * Add links to items list.
     *
     * @param array $items
     * @return array
     */
    private function addSampleUrl(array $items): array
    {
        foreach ($items as $key => $item) {
            $items[$key]['sample_url'] = $this->urlBuilder->getUrl(
                $this->routePath,
                [$this->tableKey => $item[$this->tableKey] ?? '']
            );
        }

        return $items;
    }
}
