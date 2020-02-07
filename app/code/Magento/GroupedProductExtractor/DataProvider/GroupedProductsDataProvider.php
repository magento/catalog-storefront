<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProductExtractor\DataProvider;

use Magento\GroupedProductExtractor\DataProvider\Query\LinkAttributesBuilder;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Prepare grouped products data:
 * [
 *    'grouped_product_id' => [
 *        'items' => [
 *            'linked_product_id' => [
 *                'qty' => float,
 *                'position' => int,
 *                'product' => id
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
     * @param ResourceConnection $resourceConnection
     * @param Query\LinkAttributesBuilder $linkAttributesBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LinkAttributesBuilder $linkAttributesBuilder
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->linkAttributesBuilder = $linkAttributesBuilder;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $attributes = $attributes['items'] ?? self::ATTRIBUTES;
        $select = $this->linkAttributesBuilder->build(
            $productIds,
            $attributes,
            $scopes
        );
        $linkedProducts = $this->resourceConnection->getConnection()->fetchAll($select);

        return $this->getLinkAttributes($linkedProducts);
    }

    /**
     * Get formatted list of links based on array of linked product attributes, products data and requested attributes
     *
     * @param array $linkedProducts
     * @return array
     */
    private function getLinkAttributes(array $linkedProducts): array
    {
        $links = [];
        foreach ($linkedProducts as $linkedProduct) {
            $groupedProductId = $linkedProduct['parent_id'];
            $linkedProductId = $linkedProduct['product_id'];
            $linkedProduct['product'] = $linkedProductId;
            $links[$groupedProductId]['items'][$linkedProductId] = $linkedProduct;
        }

        return $links;
    }
}
