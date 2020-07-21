<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExportApi\Api\Data\VariantInterface;
use Magento\CatalogExportApi\Api\VariantRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Decorate product variants repository to return data as plain array.
 */
class VariantRepositoryArrayDecorator implements VariantRepositoryInterface
{
    /**
     * @var VariantRepositoryInterface
     */
    private $variantRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param VariantRepositoryInterface $variantRepository
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        VariantRepositoryInterface $variantRepository,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->variantRepository = $variantRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * @inheritDoc
     */
    public function get(array $ids): array
    {
        $data = [];
        $products = $this->variantRepository->get($ids);
        foreach ($products as $product) {
            $data[] = $this->dataObjectProcessor->buildOutputDataArray($product, VariantInterface::class);
        }
        return $data;
    }
}
