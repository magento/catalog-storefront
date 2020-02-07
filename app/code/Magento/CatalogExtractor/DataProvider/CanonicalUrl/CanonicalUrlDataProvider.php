<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\CanonicalUrl;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\UrlFactory;
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
     * URL instance
     *
     * @var UrlFactory
     */
    private $urlFactory;

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
     * @param UrlFactory $urlFactory
     * @param CanonicalUrlQuery $canonicalUrlQuery
     * @param ResourceConnection $resourceConnection
     * @param ProductHelper $productHelper
     */
    public function __construct(
        UrlFactory $urlFactory,
        CanonicalUrlQuery $canonicalUrlQuery,
        ResourceConnection $resourceConnection,
        ProductHelper $productHelper
    ) {
        $this->urlFactory = $urlFactory;
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
        $url = $this->urlFactory->create()->setScope($storeId);

        foreach ($productIds as $productId) {
            $routePath = '';
            $requestPath = '';
            $routeParams = [
                '_nosid' => true
            ];
            $rewrite = $rewrites[$productId] ?? null;
            if ($rewrite) {
                $requestPath = $rewrite['request_path'];
            }

            if (!empty($requestPath)) {
                $routeParams['_direct'] = $requestPath;
            } else {
                $routePath = 'catalog/product/view';
                $routeParams['id'] = $productId;
            }

            $output[$productId]['canonical_url'] = $url->getUrl($routePath, $routeParams);
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
