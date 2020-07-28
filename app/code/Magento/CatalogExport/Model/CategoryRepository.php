<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExportApi\Api\CategoryRepositoryInterface;

/**
 * @inheritdoc
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var \Magento\CatalogDataExporter\Model\Feed\Categories
     */
    private $categoriesFeed;

    /**
     * @var \Magento\CatalogExportApi\Api\Data\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ExportConfiguration
     */
    private $exportConfiguration;

    /**
     * @param \Magento\CatalogDataExporter\Model\Feed\Categories $categoriesFeed
     * @param \Magento\CatalogExportApi\Api\Data\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param ExportConfiguration $exportConfiguration
     */
    public function __construct(
        \Magento\CatalogDataExporter\Model\Feed\Categories $categoriesFeed,
        \Magento\CatalogExportApi\Api\Data\CategoryFactory $categoryFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        ExportConfiguration $exportConfiguration
    ) {
        $this->categoriesFeed = $categoriesFeed;
        $this->categoryFactory = $categoryFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->exportConfiguration = $exportConfiguration;
    }

    /**
     * Retrieve DTO`s search result based on provided IDs
     *
     * @param array $ids
     * @return array|\Magento\CatalogExportApi\Api\Data\Category[]
     */
    public function get(array $ids): array
    {
        if (count($ids) > $this->exportConfiguration->getMaxItemsInResponse()) {
            throw new \InvalidArgumentException(
                'Max items in the response can\'t exceed '
                . $this->exportConfiguration->getMaxItemsInResponse()
                . '.'
            );
        }

        $categories = [];
        $feedData = $this->categoriesFeed->getFeedByIds($ids);

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
