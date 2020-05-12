<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExportApi\Api\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
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
     * @param \Magento\CatalogDataExporter\Model\Feed\Products $products
     * @param \Magento\CatalogExportApi\Api\Data\ProductInterfaceFactory $productFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(
        \Magento\CatalogDataExporter\Model\Feed\Products $products,
        \Magento\CatalogExportApi\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    ) {
        $this->products = $products;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productFactory = $productFactory;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function get(array $ids)
    {
        if (sizeof($ids) > $this->getMaxItemsInResponse()) {
            throw new \InvalidArgumentException(
                'Max items in the response can\'t exceed '
                    . $this->getMaxItemsInResponse()
                    . '.'
            );
        }

        $products = [];
        $feedData = $this->products->getFeedByIds($ids);
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