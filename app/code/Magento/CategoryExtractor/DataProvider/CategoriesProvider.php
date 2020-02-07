<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Category filter allows to filter collection using 'id, url_key, name' from search criteria.
 */
class CategoriesProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get categories by ids
     *
     * @param array $ids
     * @param array|null $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Catalog\Model\Category[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoriesByIds(array $ids, array $attributes = null)
    {
        $categoryCollection = $this->collectionFactory->create();
        $categoryCollection->addAttributeToFilter(CategoryInterface::KEY_IS_ACTIVE, ['eq' => 1]);
        $categoryCollection->addIdFilter($ids);
        if ($attributes) {
            $categoryCollection->addAttributeToSelect($attributes);
        }

        return $categoryCollection;
    }
}
