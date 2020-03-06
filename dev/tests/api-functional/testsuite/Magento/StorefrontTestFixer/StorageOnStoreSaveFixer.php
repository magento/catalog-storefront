<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Plugin for store saving.
 */
class StorageOnStoreSaveFixer extends \Magento\CatalogStorefrontConnector\Model\Sync\SyncStorageOnStoreSave
{
    /**
     * @inheritDoc
     *
     * Ad-hoc solution. Force run consumers after store save inside test-case
     *
     * @inheritDoc
     */
    public function afterSave(
        \Magento\Store\Model\Store $subject,
        \Magento\Store\Model\Store $result
    ): \Magento\Store\Model\Store {
        $result = parent::afterSave($subject, $result);

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
