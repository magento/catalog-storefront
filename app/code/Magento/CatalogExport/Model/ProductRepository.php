<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExportApi\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Constant value for setting max items in response
     */
    private const MAX_ITEMS_IN_RESPONSE = 250;

    /**
     * @var \Magento\CatalogDataExporter\Model\Feed\Products
     */
    private $products;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\CatalogDataExporter\Model\Feed\Products $products
     * @param \Magento\CatalogExportApi\Api\Data\ProductInterfaceFactory $productFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\CatalogDataExporter\Model\Feed\Products $products,
        \Magento\CatalogExportApi\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        LoggerInterface $logger
    ) {
        $this->products = $products;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productFactory = $productFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function get(array $ids)
    {
        if (count($ids) > $this->getMaxItemsInResponse()) {
            throw new \InvalidArgumentException(
                'Max items in the response can\'t exceed '
                    . $this->getMaxItemsInResponse()
                    . '.'
            );
        }

        $products = [];
        $feedData = $this->products->getFeedByIds($ids);
        if (empty($feedData['feed'])) {
            $this->logger->error(
                \sprintf('Cannot find products data in catalog feed with ids "%s"', \implode(',', $ids))
            );
            return $products;
        }

        foreach ($feedData['feed'] as $feedItem) {
            $product = $this->productFactory->create();
            $feedItem['id'] = $feedItem['productId'];
            $this->dataObjectHelper->populateWithArray(
                $product,
                $feedItem,
                \Magento\CatalogExportApi\Api\Data\ProductInterface::class
            );
            $products[] = $product;
        }
        return $products;
    }

    /**
     * Get max items in response
     *
     * @return int
     */
    private function getMaxItemsInResponse()
    {
        $maxItemsInResponse = (int) $this->deploymentConfig->get('catalog_export/max_items_in_response');
        return $maxItemsInResponse ?: self::MAX_ITEMS_IN_RESPONSE;
    }
}
