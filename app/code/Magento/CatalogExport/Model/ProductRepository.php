<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExport\Api\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    private const MAX_ITEMS_IN_RESPONSE = 250;

    /**
     * @var \Magento\CatalogDataExporter\Model\Feed\Products
     */
    private $products;

    /**
     * @var \Magento\CatalogExport\Api\Data\ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param \Magento\CatalogDataExporter\Model\Feed\Products $products
     * @param \Magento\CatalogExport\Api\Data\ProductInterfaceFactory $productFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\CatalogDataExporter\Model\Feed\Products $products,
        \Magento\CatalogExport\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->products = $products;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productFactory = $productFactory;
    }

    /**
     * @inheritdoc
     */
    public function get(array $ids)
    {
        if (sizeof($ids) > self::MAX_ITEMS_IN_RESPONSE) {
            throw new \InvalidArgumentException(
                'Max items in the response can\'t exceed '
                    . self::MAX_ITEMS_IN_RESPONSE
                    . '.'
            );
        }

        $products = [];
        $feedData = $this->products->getFeedByIds($ids);
        foreach ($feedData['feed'] as $feedItem) {
            $product = $this->productFactory->create();
            if (isset($feedItem['prices'])) {
                $prices = [];
                foreach ($feedItem['prices'] as $code => $price) {
                    $price['code'] = $code;
                    $prices[] = $price;
                }
                $feedItem['prices'] = $prices;
            }
            $this->dataObjectHelper->populateWithArray(
                $product,
                $feedItem,
                \Magento\CatalogExport\Api\Data\ProductInterface::class
            );
            $products[] = $product;
        }
        return $products;
    }
}