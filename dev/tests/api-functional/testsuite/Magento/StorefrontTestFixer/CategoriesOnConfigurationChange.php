<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\CatalogStorefrontConnector\Plugin\UpdateCategoriesOnConfigurationChange;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Plugin for collect category data during saving process
 */
class CategoriesOnConfigurationChange extends UpdateCategoriesOnConfigurationChange
{
    /**
     * @inheritdoc
     *
     * Ad-hoc solution. Force run consumers after category save inside test-case
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSaveConfig(
        Config $subject,
        Config $result,
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ): Config {
        $result = parent::afterSaveConfig(
            $subject,
            $result,
            $path,
            $value,
            $scope,
            $scopeId
        );
        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
