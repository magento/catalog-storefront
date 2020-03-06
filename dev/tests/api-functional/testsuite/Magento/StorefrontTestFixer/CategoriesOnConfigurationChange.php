<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\CatalogInventoryExtractor\Plugin\UpdateCategoriesOnConfigurationChange;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Override original plugin to run consumers during tests
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
        /** @var \Magento\TestFramework\Workaround\ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(\Magento\TestFramework\Workaround\ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
