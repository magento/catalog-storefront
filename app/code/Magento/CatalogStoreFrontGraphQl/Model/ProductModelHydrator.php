<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Add "model" (Product instance) to GraphQl result to support deprecated fields, which rely on legacy implementation
 */
class ProductModelHydrator
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Hydrate $value with 'model'
     *
     * @param array $value
     * @return array
     * @throws NoSuchEntityException
     */
    public function hydrate(array $value): array
    {
        if (!isset($value['model']) || !$value['model'] instanceof ProductInterface) {
            $value['model'] = $this->productRepository->getById($value['id']);
        }

        return $value;
    }
}
