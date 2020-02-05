<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductExtractor\DataProvider;

use Magento\Framework\App\ResourceConnection;
use Magento\ConfigurableProductExtractor\DataProvider\Query\Attributes\ConfigurableOptionsBuilder;

/**
 * Provide configurable attributes for specified configurable products
 * Return data in format
 * [
 *  configurable_id => [
 *      attribute_id => [
 *          product_id
 *          attribute_id
 *          attribute_code
 *          label
 *          ...
 *      ]
 *  ]
 * ]
 */
class ConfigurableAttributesProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ConfigurableOptionsBuilder
     */
    private $configurableOptionsBuilder;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ConfigurableOptionsBuilder $configurableOptionsBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ConfigurableOptionsBuilder $configurableOptionsBuilder
    ) {
        $this->configurableOptionsBuilder = $configurableOptionsBuilder;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get configurable attributes
     *
     * @param array $parentProductIds
     * @param array $requestedAttributes
     * @param array $scopes
     * @return array
     * @throws \Exception
     */
    public function provide(array $parentProductIds, array $requestedAttributes, array $scopes): array
    {
        $storeId = (int)$scopes['store'];

        /** @var \Magento\Framework\DB\Select $configurableOptionsSelect */
        $select = $this->configurableOptionsBuilder->build(
            $parentProductIds,
            $requestedAttributes['configurable_options'] ?? [],
            $storeId
        );

        $configurableAttributes = [];
        $statement = $this->resourceConnection->getConnection()->query($select);

        while ($row = $statement->fetch()) {
            $configurableAttributes[$row['product_id']][$row['attribute_id']] = $row;
        }

        return $configurableAttributes;
    }
}
