<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Catalog\Model\Category;
use Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataOnMove;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\ConsumerInvoker;

/**
 * Plugin for collect category data during saving process
 */
class CategoryOnMove extends CollectCategoriesDataOnMove
{
    /**
     * @inheritdoc
     *
     * Ad-hoc solution. Force run consumers after category save inside test-case
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterMove(
        Category $category,
        Category $result
    ): Category {
        $result = parent::afterMove($category, $result);

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
