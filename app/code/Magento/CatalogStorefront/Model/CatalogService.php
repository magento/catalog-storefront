<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\Image;
use Magento\CatalogStorefrontApi\Api\Data\ProductInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResult;
use \Magento\CatalogStorefrontApi\Api\Data\ProductsGetResultInterface;
use \Magento\CatalogStorefrontApi\Api\Data\ImportProductsResponseInterface;
use Magento\CatalogStorefront\DataProvider\ProductDataProvider;
use Magento\Framework\Api\DataObjectHelper;

/**
 * @inheritdoc
 */
class CatalogService implements CatalogServerInterface
{
    /**
     * @var ProductDataProvider
     */
    private $dataProvider;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * CatalogService constructor.
     * @param ProductDataProvider $dataProvider
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ProductDataProvider $dataProvider,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->dataProvider = $dataProvider;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    public function GetProducts(ProductsGetRequestInterface $request
    ): ProductsGetResultInterface {
        if (is_null($request->getStore()) || empty($request->getStore())) {
            return $this->processErrors([_('Store id is not present in Search Criteria. Please add missing info.')]);
        }
        $result = new ProductsGetResult();
        $products = [];
        if (!empty($request->getIds())) {
            $rawItems = $this->dataProvider->fetch(
                $request->getIds(),
                $request->getAttributeCodes(),
                ['store' => $request->getStore()]
            );

            foreach ($rawItems as $item) {
                $product = new \Magento\CatalogStorefrontApi\Api\Data\Product();
                $item['description'] = $item['description']['html'] ?? "";

                $this->dataObjectHelper->populateWithArray($product, $item, ProductInterface::class);
                $product = $this->setImage('image', $item, $product);
                $product = $this->setImage('small_image', $item, $product);
                $product = $this->setImage('thumbnail', $item, $product);

                $products[] = $product;
            }

        }
        $result->setData($products);

        return $result;
    }

    private function setImage(string $key, array $rawData, ProductInterface $product): ProductInterface
    {
        if (empty($rawData[$key])) {
            return $product;
        }

        $image = new Image();
        $image->setUrl($rawData[$key]['url'] ?? "");
        $image->setLabel($rawData[$key]['label'] ?? "");
        $parts = explode('_', $key);
        $parts = array_map("ucfirst", $parts);
        $methodName = 'set' . implode('', $parts);

        $product->$methodName($image);
        return $product;
    }

    public function ImportProducts(ImportProductsRequestInterface $request
    ): ImportProductsResponseInterface {
        // TODO: Implement ImportProducts() method.
    }
}
