<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
    public function execute(array $ids)
    {
        // TODO: Why do we need this class? We suppose to do REST call to ExportAPI which will return json, not object.
        // If this is implementation for in-process call we can simplify it and return array from productRepo
        // So why we need transform objects?

        $products = $this->productRepository->get($ids);
        $data = [];
        foreach ($products as $product) {
            $data[] = $this->dataObjectProcessor->buildOutputDataArray($product, Product::class);
        }
        return $data;
        // TODO: remove temporary solution after https://github.com/magento/catalog-storefront/issues/157
        return $products;
//        $data = [];
//        foreach ($products as $product) {
//            $data[] = $this->dataObjectProcessor->buildOutputDataArray($product, Product::class);
//        }
//        return $data;
    }

    /**
     * @inheritDoc
     *
     * @param string[] $ids
     * @return array
     */
    public function getDeleted(array $ids): array
    {
        return (array)$this->productRepository->getDeleted($ids);
    }
}
