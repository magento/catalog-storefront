<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide all ids for catalog entities: product and catalog. Used during full reindex action
 */
class CatalogEntityIdsProvider
{
    /**
     * Default batch size
     * @var int
     */
    private const BATCH_SIZE = 1000;

    /**
     * Product entity id field
     */
    private const PRODUCT_ENTITY_ID = 'entity_id';

    /**
     * Category entity id field
     */
    private const CATEGORY_ENTITY_ID = 'entity_id';

    /**
     * @var ProductCollectionFactory
     */
    private $productsCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var \Magento\Framework\DB\Query\Generator
     */
    private $generator;

    /**
     * @param ProductCollectionFactory $productsCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Query\Generator $generator
     */
    public function __construct(
        ProductCollectionFactory $productsCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Query\Generator $generator
    ) {
        $this->productsCollectionFactory = $productsCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->generator = $generator;
        $this->storeManager = $storeManager;
    }

    /**
     * Get all product ids
     *
     * @param int $storeId
     * @return \Generator
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductIds(int $storeId): \Generator
    {
        $collection = $this->productsCollectionFactory->create();
        $collection->addWebsiteFilter($this->storeManager->getStore($storeId)->getWebsiteId());
        $collection->getSelect()->reset(Select::COLUMNS);
        $collection->getSelect()->columns(self::PRODUCT_ENTITY_ID);
        $selects = $this->generator->generate(self::PRODUCT_ENTITY_ID, $collection->getSelect(), self::BATCH_SIZE);
        foreach ($selects as $select) {
            $products = $collection->getConnection()->fetchCol($select);
            if ($products) {
                yield $products;
            }
        }
    }

    /**
     * Get all category ids
     *
     * @param int $storeId
     * @return \Generator
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryIds(int $storeId): \Generator
    {
        $collection = $this->categoryCollectionFactory->create();
        //load only from store root
        $rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();
        $collection->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']);
        $collection->getSelect()->reset(Select::COLUMNS);
        $collection->getSelect()->columns(self::CATEGORY_ENTITY_ID);

        $selects = $this->generator->generate(self::CATEGORY_ENTITY_ID, $collection->getSelect(), self::BATCH_SIZE);
        foreach ($selects as $select) {
            $categories = $collection->getConnection()->fetchCol($select);
            if ($categories) {
                yield $categories;
            }
        }
        yield [$rootId];
    }
}
