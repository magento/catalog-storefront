<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Add "model" (Catalog instance) to GraphQl result to support deprecated fields, which rely on legacy implementation
 */
class CategoryModelHydrator
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $productRepository
     */
    public function __construct(CategoryRepositoryInterface $productRepository)
    {
        $this->categoryRepository = $productRepository;
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
        if (!isset($value['model']) || !$value['model'] instanceof CategoryInterface) {
            $value['model'] = $this->categoryRepository->get($value['id']);
        }

        return $value;
    }
}
