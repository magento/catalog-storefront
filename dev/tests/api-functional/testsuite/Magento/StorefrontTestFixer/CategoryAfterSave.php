<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\Category;

/**
 * Plugin for collect category data during saving process
 */
class CategoryAfterSave extends \Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataForUpdate
{
    /**
     * @inheritdoc
     *
     * Ad-hoc solution. Force run consumers after category save inside test-case
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave(
        CategoryResource $subject,
        CategoryResource $result,
        Category $category
    ): CategoryResource {
        $result = parent::afterSave($subject, $result, $category);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
