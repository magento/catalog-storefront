<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExport\Api\Data\EntitiesRequestInterface;
use Magento\CatalogExportApi\Api\CategoryRepositoryInterface;
use Magento\CatalogExportApi\Api\Data\CategoryFactory;
use Magento\DataExporter\Model\FeedPool;
use Magento\Framework\Api\DataObjectHelper;

/**
 * @inheritdoc
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ExportConfiguration
     */
    private $exportConfiguration;

    /**
     * @var FeedPool
     */
    private $feedPool;

    /**
     * @param FeedPool $feedPool
     * @param CategoryFactory $categoryFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ExportConfiguration $exportConfiguration
     */
    public function __construct(
        FeedPool $feedPool,
        CategoryFactory $categoryFactory,
        DataObjectHelper $dataObjectHelper,
        ExportConfiguration $exportConfiguration
    ) {
        $this->feedPool = $feedPool;
        $this->categoryFactory = $categoryFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->exportConfiguration = $exportConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function get(EntitiesRequestInterface $request)
    {
        $storeViewCodes = $request->getStoreViewCodes();

        // Transform request data into entity_id => attributes relation
        $productsRequestArray = [];
        foreach ($request->getEntitiesRequestData() as $requestData) {
            $productsRequestArray[$requestData->getEntityId()] = $requestData->getAttributeCodes();
        }

        $ids = \array_keys($productsRequestArray);

        if (count($ids) > $this->exportConfiguration->getMaxItemsInResponse()) {
            throw new \InvalidArgumentException(
                'Max items in the response can\'t exceed '
                . $this->exportConfiguration->getMaxItemsInResponse()
                . '.'
            );
        }

        $categories = [];
        $feedData = $this->feedPool->getFeed('categories')->getFeedByIds($ids, $storeViewCodes, $productsRequestArray);

        foreach ($feedData['feed'] as $feedItem) {
            $category = $this->categoryFactory->create();
            $feedItem['id'] = $feedItem['categoryId'];

            $this->dataObjectHelper->populateWithArray(
                $category,
                $feedItem,
                \Magento\CatalogExportApi\Api\Data\Category::class
            );
            $categories[] = $category;
        }
        return $categories;
    }
}
