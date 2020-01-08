<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\UrlRewrites;

use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogProduct\DataProvider\UrlRewrites\Query\UrlRewritesQuery;

/**
 * Url Rewrites data provider, used for GraphQL resolver processing.
 */
class UrlRewritesDataProvider implements DataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var UrlRewritesQuery
     */
    private $urlRewritesQuery;

    /**
     * Url Rewrites Attributes
     */
    private const URL_REWRITES_ATTRIBUTES = [
        'url',
        'parameters' => ['name', 'value'],
    ];

    /**
     * @param ResourceConnection $resourceConnection
     * @param UrlRewritesQuery $urlRewritesQuery
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        UrlRewritesQuery $urlRewritesQuery
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->urlRewritesQuery = $urlRewritesQuery;
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $storeId = (int)$scopes['store'];

        // get url rewrites for products
        $urlRewrites = $this->getUrlRewrites($productIds, $storeId);

        if (!empty($attributes['url_rewrites'])) {
            $urlRewriteAttributes = $attributes['url_rewrites'];
        } else {
            $urlRewriteAttributes = self::URL_REWRITES_ATTRIBUTES;
        }

        $output = [];
        foreach ($urlRewrites as $item) {
            $urlRewrite = [];

            if (\in_array('url', $urlRewriteAttributes, true)) {
                $urlRewrite['url'] = $item['request_path'];
            }
            if (isset($urlRewriteAttributes['parameters'])) {
                $urlRewrite['parameters'] = $this->getUrlParameters($item['target_path']);
            }

            $output[$item['entity_id']]['url_rewrites'][] = $urlRewrite;
        }

        return $output;
    }

    /**
     * Get url rewrites for products and given store.
     *
     * @param int[] $productIds
     * @param int $storeId
     * @return array
     */
    private function getUrlRewrites(array $productIds, int $storeId): array
    {
        // get url rewrites of products
        $urlRewritesSelect = $this->urlRewritesQuery->getQuery($productIds, $storeId);
        $connection = $this->resourceConnection->getConnection();

        return $connection->fetchAll($urlRewritesSelect);
    }

    /**
     * Parses target path and extracts parameters
     *
     * @param string $targetPath
     * @return array
     */
    private function getUrlParameters(string $targetPath): array
    {
        $urlParameters = [];
        $targetPathParts = explode('/', trim($targetPath, '/'));

        $targetPathPartsCount = count($targetPathParts);
        for ($i = 3; $i < $targetPathPartsCount - 1; $i += 2) {
            $urlParameters[] = [
                'name' => $targetPathParts[$i],
                'value' => $targetPathParts[$i + 1]
            ];
        }

        return $urlParameters;
    }
}
