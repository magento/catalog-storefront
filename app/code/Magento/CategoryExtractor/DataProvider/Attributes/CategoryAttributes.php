<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider\Attributes;

use Magento\Framework\App\ResourceConnection;
use Magento\CatalogExtractor\DataProvider\Query\Category\CategoryAttributeQueryBuilder;

/**
 * Provide category attributes for specified category ids and attributes
 */
class CategoryAttributes
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategoryAttributeQueryBuilder
     */
    private $categoryAttributeQueryBuilder;

    /**
     * @var CategoryAttributesMapper
     */
    private $attributesMapper;

    /**
     * @param ResourceConnection $resourceConnection
     * @param CategoryAttributeQueryBuilder $categoryAttributeQueryBuilder
     * @param CategoryAttributesMapper $attributesMapper
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CategoryAttributeQueryBuilder $categoryAttributeQueryBuilder,
        CategoryAttributesMapper $attributesMapper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->categoryAttributeQueryBuilder = $categoryAttributeQueryBuilder;
        $this->attributesMapper = $attributesMapper;
    }

    /**
     * Get category attributes
     *
     * @param array $entityIds
     * @param array $attributeCodes
     * @param int $storeId
     * @return array
     * @throws \Exception
     */
    public function getAttributesData(array $entityIds, array $attributeCodes, int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $attributes = $this->attributesMapper->getAttributesValues(
            $connection->fetchAll(
                $this->categoryAttributeQueryBuilder->build($entityIds, $attributeCodes, $storeId)
            )
        );
        foreach ($attributes as &$categoryAttributes) {
            $categoryAttributes = \array_intersect_key($categoryAttributes, \array_flip($attributeCodes));
        }

        return $attributes;
    }
}
