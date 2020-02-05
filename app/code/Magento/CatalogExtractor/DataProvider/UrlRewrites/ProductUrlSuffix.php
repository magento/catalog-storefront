<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\UrlRewrites;

use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Returns the url suffix for product
 */
class ProductUrlSuffix implements DataProviderInterface
{
    /**
     * System setting for the url suffix for products
     *
     * @var string
     */
    private static $xmlPathProductUrlSuffix = 'catalog/seo/product_url_suffix';

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    private $productUrlSuffix = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $storeId = (int)$scopes['store'];
        $productSuffix = $this->getProductUrlSuffix($storeId);

        $output = [];
        foreach ($productIds as $product) {
            $output[$product]['url_suffix'] = $productSuffix;
        }

        return $output;
    }

    /**
     * Retrieve product url suffix by store.
     *
     * @param int $storeId
     * @return string
     */
    private function getProductUrlSuffix(int $storeId): string
    {
        if (!isset($this->productUrlSuffix[$storeId])) {
            $this->productUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                self::$xmlPathProductUrlSuffix,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->productUrlSuffix[$storeId];
    }
}
