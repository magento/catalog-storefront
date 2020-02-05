<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryStorefront\DataProvider;

use Magento\CatalogInventoryStorefront\DataProvider\Query\StockStatusBuilder;
use Magento\CatalogStorefrontConnector\DataProvider\DataProviderInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Get stock status
 */
class StockStatusProvider implements DataProviderInterface
{
    /**
     * In stock
     */
    private const VALUE_IN_STOCK = 'IN_STOCK';

    /**
     * Out of stock
     */
    private const VALUE_OUT_OF_STOCK = 'OUT_OF_STOCK';

    /**
     * In Stock status
     * @see \Magento\CatalogInventory\Api\Data\StockStatusInterface::STATUS_IN_STOCK
     */
    private const STATUS_IN_STOCK = 1;

    /**
     * @var StockStatusBuilder
     */
    private $stockStatusBuilder;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param StockStatusBuilder $stockStatusBuilder
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        StockStatusBuilder $stockStatusBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->stockStatusBuilder = $stockStatusBuilder;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     * @throws \Zend_Db_Statement_Exception
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $output = [];

        $connection = $this->resourceConnection->getConnection();
        $statement = $connection->query(
            $this->stockStatusBuilder->build($productIds)
        );

        while ($data = $statement->fetch()) {
            $output[$data['product_id']]['stock_status'] =
                (int)$data['stock_status'] === self::STATUS_IN_STOCK
                    ? self::VALUE_IN_STOCK
                    : self::VALUE_OUT_OF_STOCK;

        }
        return $output;
    }
}
