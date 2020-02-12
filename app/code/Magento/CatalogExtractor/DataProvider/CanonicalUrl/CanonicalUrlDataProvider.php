<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\CanonicalUrl;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\CatalogExtractor\DataProvider\CanonicalUrl\Query\CanonicalUrlQuery;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Helper\Product as ProductHelper;

/**
 * Data Provider for getting products canonical URL
 */
class CanonicalUrlDataProvider implements DataProviderInterface
{
    /**
     * @var CanonicalUrlQuery
     */
    private $canonicalUrlQuery;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @param CanonicalUrlQuery $canonicalUrlQuery
     * @param ResourceConnection $resourceConnection
     * @param ProductHelper $productHelper
     */
    public function __construct(
        CanonicalUrlQuery $canonicalUrlQuery,
        ResourceConnection $resourceConnection,
        ProductHelper $productHelper
    ) {
        $this->canonicalUrlQuery = $canonicalUrlQuery;
        $this->resourceConnection = $resourceConnection;
        $this->productHelper = $productHelper;
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $storeId = (int)$scopes['store'];
        if (!$this->productHelper->canUseCanonicalTag($storeId)) {
            return [];
        }

        $output = [];
        $rewrites = $this->getRewrites($productIds, $storeId);

        foreach ($productIds as $productId) {
            $output[$productId]['canonical_url'] = $rewrites[$productId]['request_path'] ?? null;
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
    private function getRewrites(array $productIds, int $storeId): array
    {
        // get url rewrites of products
        $urlRewritesSelect = $this->canonicalUrlQuery->getQuery(
            $productIds,
            $storeId,
            ProductUrlRewriteGenerator::ENTITY_TYPE
        );
        $connection = $this->resourceConnection->getConnection();

        $rawCanonicalUrls = $connection->fetchAll($urlRewritesSelect);

        $canonicalUrls = [];
        foreach ($rawCanonicalUrls as $url) {
            $canonicalUrls[$url['entity_id']] = $url;
        }

        return $canonicalUrls;
    }
}
