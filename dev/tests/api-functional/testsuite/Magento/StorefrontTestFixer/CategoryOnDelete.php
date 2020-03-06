<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Model\AbstractModel;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Override original plugin to run consumers during tests
 */
class CategoryOnDelete extends \Magento\CatalogStorefrontConnector\Plugin\CategoryOnDelete
{
    /**
     * @inheritdoc
     *
     * Ad-hoc solution. Force run consumers after category save inside test-case
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterDelete(
        CategoryResource $subject,
        CategoryResource $result,
        AbstractModel $category
    ): CategoryResource {
        $result = parent::afterDelete($subject, $result, $category);

        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\TestFramework\Workaround\ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(\Magento\TestFramework\Workaround\ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
