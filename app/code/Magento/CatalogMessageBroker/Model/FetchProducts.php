<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExportApi\Api\Data\Product;
use Magento\CatalogExportApi\Api\ProductRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * @inheritdoc
 */
class FetchProducts implements FetchProductsInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->productRepository = $productRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * @inheritdoc
     *
     * @param string[] $ids
     */
    public function getByIds(array $ids)
    {
        $products = $this->productRepository->get($ids);
        $data = [];
        foreach ($products as $product) {
            $data[] = $this->dataObjectProcessor->buildOutputDataArray($product, Product::class);
        }
        return $data;
    }

    /**
     * @inheritDoc
     *
     * @param string[] $ids
     * @return array
     */
    public function getDeleted(array $ids): array
    {
        $data = [];
        foreach ($this->productRepository->getDeleted($ids) as $category) {
            $data[] = [
                'product_id' => $category['productId'],
                'store_view_code' => $category['storeViewCode'],
            ];
        }
        return $data;
    }
}
