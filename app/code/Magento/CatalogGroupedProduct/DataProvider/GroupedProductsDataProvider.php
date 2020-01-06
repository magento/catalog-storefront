<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGroupedProduct\DataProvider;

use Magento\CatalogGroupedProduct\DataProvider\Query\LinkAttributesBuilder;
use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Prepare grouped products data:
 * [
 *    'grouped_product_id' => [
 *        'items' => [
 *            'linked_product_id' => [
 *                'qty' => float,
 *                'position' => int,
 *                'product' => product_data[]
 *            ]
 *        ]
 *    ]
 *    ...
 *
 * ]
 */
class GroupedProductsDataProvider implements DataProviderInterface
{
    /**
     * Grouped items attributes
     */
    private const ATTRIBUTES = [
        'qty',
        'position',
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Query\LinkAttributesBuilder
     */
    private $linkAttributesBuilder;

    /**
     * @var DataProviderInterface
     */
    private $generalDataProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Query\LinkAttributesBuilder $linkAttributesBuilder
     * @param DataProviderInterface $generalDataProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LinkAttributesBuilder $linkAttributesBuilder,
        DataProviderInterface $generalDataProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->linkAttributesBuilder = $linkAttributesBuilder;
        $this->generalDataProvider = $generalDataProvider;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $groupedAttributes = $attributes['items'] ?? [];
        // TODO: handle ad-hoc solution MC-29791
        $select = $this->linkAttributesBuilder->build(
            $productIds,
            $groupedAttributes ?: self::ATTRIBUTES,
            $scopes
        );
        $linkedProducts = $this->resourceConnection->getConnection()->fetchAll($select);
        $productsInfo = [];
        // TODO: handle ad-hoc solution MC-29791
        if (!empty($groupedAttributes)) {
            $linkIds = \array_unique(\array_column($linkedProducts, 'product_id'));
            $productsInfo = $this->getProductsInfo($linkIds, $groupedAttributes ?: self::ATTRIBUTES, $scopes);
        }

        $productLinks = $this->getLinkAttributes($linkedProducts, $productsInfo, $groupedAttributes);

        return $productLinks;
    }

    /**
     * Get formatted list of links based on array of linked product attributes, products data and requested attributes
     *
     * @param array $linkedProducts
     * @param array $productsInfo
     * @param array $attributes
     * @return array
     */
    private function getLinkAttributes(array $linkedProducts, array $productsInfo, array $attributes): array
    {
        $links = [];
        foreach ($linkedProducts as $linkedProduct) {
            $linkAttributes = [];
            $groupedProductId = $linkedProduct['parent_id'];
            $linkedProductId = $linkedProduct['product_id'];

            // TODO: handle ad-hoc solution MC-29791
            if (empty($attributes)) {
                $linkedProduct['product'] = $linkedProductId;
                $links[$groupedProductId]['items'][$linkedProductId] = $linkedProduct;
                continue;
            }

            foreach ($attributes as $attributeKey => $attributeName) {
                if (\is_string($attributeName)) {
                    $linkAttributes[$attributeName] = $linkedProduct[$attributeName] ?? null;
                } elseif ($attributeKey === 'product' && isset($productsInfo[$linkedProductId])) {
                    $linkAttributes[$attributeKey] = $productsInfo[$linkedProductId];
                }
            }
            $links[$groupedProductId]['items'][$linkedProductId] = $linkAttributes;
        }

        return $links;
    }

    /**
     * Prepare product information with requested product attributes
     *
     * @param array $productIds
     * @param array $attributes
     * @param array $scopes
     * @return array
     */
    private function getProductsInfo(array $productIds, array $attributes, array $scopes): array
    {
        $productsInfo = [];
        if (!empty($attributes['product'])) {
            $productAttributes = $this->generalDataProvider->fetch($productIds, $attributes['product'], $scopes);
            foreach ($productAttributes as $productId => $productAttributesData) {
                $productsInfo[$productId] = $productAttributesData;
            }
        }

        return $productsInfo;
    }
}
