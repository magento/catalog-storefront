<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExportApi\Api\CategoryRepositoryInterface;
use Magento\CatalogExportApi\Api\Data\Category;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * @inheritdoc
 */
class FetchCategories implements FetchCategoriesInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritdoc
     */
    public function getByIds(array $ids): array
    {
        $data = [];
        $categories = $this->categoryRepository->get($ids);
        foreach ($categories as $category) {
            $data[] = $this->dataObjectProcessor->buildOutputDataArray($category, Category::class);
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getDeleted(array $ids): array
    {
        $data = [];
        //todo: this loop is unnecessary other than data key formatting. See if can move this to generic feed?.
        foreach ($this->categoryRepository->getDeleted($ids) as $category) {
            $data[] = [
                'category_id' => $category['categoryId'],
                'store_view_code' => $category['storeViewCode'],
            ];
        }
        return $data;
    }
}
