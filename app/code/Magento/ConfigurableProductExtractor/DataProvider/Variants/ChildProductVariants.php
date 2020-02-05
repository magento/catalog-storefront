<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductExtractor\DataProvider\Variants;

use Magento\CatalogStorefrontConnector\DataProvider\DataProviderInterface as GeneralDataProvider;

/**
 * Prepare variants for configurable products
 */
class ChildProductVariants
{
    /**
     * @var GeneralDataProvider
     */
    private $generalDataProvider;

    /**
     * @param GeneralDataProvider $generalDataProvider
     */
    public function __construct(
        GeneralDataProvider $generalDataProvider
    ) {

        $this->generalDataProvider = $generalDataProvider;
    }

    /**
     * Get product variants assigned to configurable products
     *
     * @param array $products
     * @param array $productAttributes
     * @param array $scopes
     * @return array
     */
    public function getProductVariants(array $products, array $productAttributes, array $scopes): array
    {
        $childrenMap = [];
        $variantIds = array_unique(array_column($products, 'variant_id'));
        // TODO: handle ad-hoc solution MC-29791
        if (empty($productAttributes)) {
            foreach ($products as $child) {
                $variantId = $child['variant_id'] ?? null;
                if ($variantId) {
                    $childrenMap[$child['parent_id']]['variants'][$variantId]['product'] = $variantId;
                }
            }

            return $childrenMap;
        }

        $attributesData = $this->generalDataProvider->fetch($variantIds, $productAttributes, $scopes);
        foreach ($products as $child) {
            $variantId = $child['variant_id'] ?? null;
            if (isset($attributesData[$variantId])) {
                $childrenMap[$child['parent_id']]['variants'][$variantId]['product'] = $attributesData[$variantId];
            }
        }

        return $childrenMap;
    }
}
