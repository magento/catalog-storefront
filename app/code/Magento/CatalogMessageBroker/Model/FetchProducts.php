<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExportApi\Api\Data\ProductInterface;
use Magento\CatalogExportApi\Api\ProductRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

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
     */
    public function execute(array $ids)
    {
        $data = [];
        $products = $this->productRepository->get($ids);
        foreach ($products as $product) {
            $data[] = $this->dataObjectProcessor->buildOutputDataArray($product, ProductInterface::class);
        }
        return $data;
    }
}
