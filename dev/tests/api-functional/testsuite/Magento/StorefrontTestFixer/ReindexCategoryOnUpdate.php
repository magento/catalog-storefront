<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogStorefrontConnector\Plugin\ReindexOnConfigurationChange;
use Magento\Framework\Model\AbstractModel;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Plugin for collect category data during saving process
 */
class ReindexCategoryOnUpdate extends ReindexOnConfigurationChange
{
    /**
     * @inheritdoc
     *
     * Ad-hoc solution. Force run consumers after category save inside test-case
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSaveConfig(
        $path,
        $value,
        $scope,
        $scopeId
    ): void {
        parent::afterSaveConfig(
            $path,
            $value,
            $scope,
            $scopeId
        );
        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);
    }
}
