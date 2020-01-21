<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\Link;

use Magento\Framework\App\ResourceConnection;
use Magento\CatalogProduct\DataProvider\DataProviderInterface;
use Magento\Catalog\Model\Product\LinkTypeProvider;

/**
 * Product product links data provider, used for GraphQL resolver processing.
 */
class Provider implements DataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Query
     */
    private $productLinksQuery;

    /**
     * @var LinkTypeProvider
     */
    private $linkTypeProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Query $productLinksQuery
     * @param LinkTypeProvider $productLinkTypeList
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Query $productLinksQuery,
        LinkTypeProvider $productLinkTypeList
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productLinksQuery = $productLinksQuery;
        $this->linkTypeProvider = $productLinkTypeList;
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $connection = $this->resourceConnection->getConnection();
        $productLinksSelect = $this->productLinksQuery->getQuery($productIds);
        $productLinks = $connection->fetchAll($productLinksSelect);
        $linkTypes = array_flip($this->linkTypeProvider->getLinkTypes());

        $output = [];
        foreach ($productLinks as $key => $productLink) {
            $productLink['link_type'] = $linkTypes[$productLink['link_type_id']] ?? null;
            if (isset($attributes['product_links'])) {
                $productLink = array_intersect_key($productLink, array_flip($attributes['product_links']));
            }
            $output[$productLinks[$key]['product_id']]['product_links'][] = $productLink;
        }

        return $output;
    }
}
