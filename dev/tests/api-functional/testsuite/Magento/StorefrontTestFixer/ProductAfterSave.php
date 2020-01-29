<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\CatalogStorefrontConnector\Plugin\CollectProductsDataOnSave;

/**
 * Plugin for collect products data product save. Handle case when indexer mode is set to "runtime"
 */
class ProductAfterSave extends CollectProductsDataOnSave
{
    /**
     * Ad-hoc solution. Force run consumers after product save inside test-case
     *
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Catalog\Model\ResourceModel\Product $result,
        \Magento\Catalog\Model\Product $product
    ): \Magento\Catalog\Model\ResourceModel\Product {
        $result = parent::afterSave($subject, $result, $product);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
