<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\DataProvider;

use Magento\Framework\App\ResourceConnection;
use Magento\CatalogStorefrontConnector\DataProvider\Query\Product\ProductAttributeQueryBuilder;
use Magento\CatalogStorefrontConnector\DataProvider\Query\AttributesDataConverter;

/**
 * Provide data for EAV attributes. Default data provider for product items
 */
class EavAttributeProvider implements DataProviderInterface
{
    /**
     * @var ProductAttributeQueryBuilder
     */
    private $productsQuery;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AttributesDataConverter
     */
    private $attributesDataConverter;

    /**
     * @param ProductAttributeQueryBuilder $productsQuery
     * @param ResourceConnection $resourceConnection
     * @param AttributesDataConverter $attributesDataConverter
     */
    public function __construct(
        ProductAttributeQueryBuilder $productsQuery,
        ResourceConnection $resourceConnection,
        AttributesDataConverter $attributesDataConverter
    ) {
        $this->productsQuery = $productsQuery;
        $this->resourceConnection = $resourceConnection;
        $this->attributesDataConverter = $attributesDataConverter;
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $connection = $this->resourceConnection->getConnection();
        $productsQuery = $this->productsQuery->build($productIds, $attributes, (int)$scopes['store']);

        return $this->attributesDataConverter->convert(
            $connection->fetchAll($productsQuery)
        );
    }
}
