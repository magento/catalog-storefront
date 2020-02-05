<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventoryExtractor\DataProvider;

use Magento\CatalogInventoryExtractor\DataProvider\Query\StockItemBuilder;
use Magento\CatalogStorefrontConnector\DataProvider\DataProviderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Get product qty left when "Catalog > Inventory > Stock Options > Only X left Threshold" is greater than 0
 */
class OnlyXLeftInStockProvider implements DataProviderInterface
{
    /**
     * Max qty config path
     */
    private const XML_PATH_MIN_QTY = 'cataloginventory/item_options/min_qty';

    /**
     * Threshold qty config path
     */
    private const XML_PATH_STOCK_THRESHOLD_QTY = 'cataloginventory/options/stock_threshold_qty';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockItemBuilder
     */
    private $stockItemBuilder;

    /**
     * @param StockItemBuilder $stockItemBuilder
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StockItemBuilder $stockItemBuilder,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->stockItemBuilder = $stockItemBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $storeId = (int)$scopes['store'];
        $thresholdQty = (float)$this->getConfigValue(self::XML_PATH_STOCK_THRESHOLD_QTY, $storeId);
        if ($thresholdQty === 0.0) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        $statement = $connection->query(
            $this->stockItemBuilder->build($productIds)
        );

        $output = [];
        while ($data = $statement->fetch()) {
            $output[$data['product_id']]['only_x_left_in_stock'] = $this->getOnlyXLeftInStock($data, $storeId);
        }

        return $output;
    }

    /**
     * Get only X left in stock
     *
     * @param array $stockItem
     * @param int $storeId
     * @return float|null
     */
    private function getOnlyXLeftInStock(array $stockItem, int $storeId): ?float
    {
        $stockCurrentQty = $stockItem['qty'] ?? 0;

        if ($stockCurrentQty <= 0) {
            return null;
        }

        $stockLeft = $stockCurrentQty - $this->getMinQty($stockItem, $storeId);
        $thresholdQty = (float)$this->getConfigValue(self::XML_PATH_STOCK_THRESHOLD_QTY, $storeId);

        if ($stockLeft <= $thresholdQty) {
            return (float)$stockLeft;
        }

        return null;
    }

    /**
     * Retrieve minimal quantity available for stock item
     *
     * @param array $stockItem
     * @param int $storeId
     * @return float
     */
    public function getMinQty(array $stockItem, int $storeId): float
    {
        if ((bool)$stockItem['use_config_min_qty']) {
            $minQty = (float)$this->getConfigValue(self::XML_PATH_MIN_QTY, $storeId);
        } else {
            $minQty = (float)$stockItem['min_qty'];
        }

        return $minQty;
    }

    /**
     * Get config value
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    private function getConfigValue(string $path, int $storeId)
    {
        if (!isset($this->config[$path])) {
            $this->config[$path] = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
        }

        return $this->config[$path];
    }
}
