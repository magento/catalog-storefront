<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider;

use Magento\Catalog\Model\Product\Link;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface as GeneralDataProvider;
use Magento\CatalogExtractor\DataProvider\Product\RelatedProduct;

/**
 * Related Products Data Provider
 */
class RelatedProductsDataProvider implements DataProviderInterface
{
    /**
     * @var RelatedProduct
     */
    private $relatedProductDataProvider;

    /**
     * @var GeneralDataProvider
     */
    private $generalDataProvider;

    /**
     * @var array
     */
    private $relationMap = [
        'related_products' => Link::LINK_TYPE_RELATED,
        'crosssell_products' => Link::LINK_TYPE_CROSSSELL,
        'upsell_products' => Link::LINK_TYPE_UPSELL
    ];

    /**
     * @param RelatedProduct $relatedProductDataProvider
     * @param GeneralDataProvider $generalDataProvider
     */
    public function __construct(
        RelatedProduct $relatedProductDataProvider,
        GeneralDataProvider $generalDataProvider
    ) {
        $this->relatedProductDataProvider = $relatedProductDataProvider;
        $this->generalDataProvider = $generalDataProvider;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $relations = [];
        foreach ($attributes as $productRelation => $fields) {
            $relations[$productRelation] = $this->findRelations(
                $productIds,
                $fields,
                $this->getLinkType($productRelation),
                $scopes
            );
        }

        //Matching products with related products.
        $relationsData = [];
        foreach ($relations as $relationAttribute => $productRelations) {
            foreach ($productRelations as $productId => $productData) {
                $relationsData[$productId][$relationAttribute] = $productData;
            }
        }

        return $relationsData;
    }

    /**
     * Find related products.
     *
     * @param int[] $products
     * @param string[] $loadAttributes
     * @param int $linkType
     * @param array $scopes
     * @return \Magento\Catalog\Api\Data\ProductInterface[][]
     */
    private function findRelations(array $products, array $loadAttributes, int $linkType, array $scopes): array
    {
        //Loading relations
        $relations = $this->relatedProductDataProvider->getRelations($products, $linkType);
        if (!$relations) {
            return [];
        }
        $relatedIds = array_values($relations);
        $relatedIds = array_unique(array_merge(...$relatedIds));

        $relatedProducts = $this->generalDataProvider->fetch($relatedIds, $loadAttributes, $scopes);

        //Matching products with related products.
        $relationsData = [];
        foreach ($relations as $productId => $relatedIds) {
            $relationsData[$productId] = array_map(
                function ($id) use ($relatedProducts) {
                    return $relatedProducts[$id];
                },
                $relatedIds
            );
        }

        return $relationsData;
    }

    /**
     * @param string $productRelation
     * @return int
     */
    private function getLinkType(string $productRelation): int
    {
        return $this->relationMap[$productRelation];
    }
}