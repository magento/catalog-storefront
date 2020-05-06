<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogStorefront\Model;

use Magento\CatalogExport\Api\Data\ProductInterface;
use Magento\CatalogExport\Api\ProductRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

class ProductRetriever implements ProductRetrieverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
     * @param string[]
     * @return ProductInterface[]
     */
    public function retrieve(array $ids)
    {
        $data = [];
        $products = $this->productRepository->get($ids);
        foreach ($products as $product) {
            $data[] = $this->dataObjectProcessor->buildOutputDataArray($product, ProductInterface::class);
        }
        return $data;
    }
}
