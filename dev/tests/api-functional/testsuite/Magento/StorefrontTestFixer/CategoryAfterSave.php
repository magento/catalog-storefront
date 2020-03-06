<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataOnSave;
use Magento\Framework\Model\AbstractModel;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Override original plugin to run consumers during tests
 */
class CategoryAfterSave extends CollectCategoriesDataOnSave
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
        AbstractModel $category
    ): CategoryResource {
        $result = parent::afterSave($subject, $result, $category);

        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\TestFramework\Workaround\ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(\Magento\TestFramework\Workaround\ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
