<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Catalog\Model\Indexer\Product\Category\Action\Rows;
use Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataForUpdate;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Override original plugin to run consumers during tests
 */
class CategoryOnUpdate extends CollectCategoriesDataForUpdate
{
    /**
     * @inheritdoc
     *
     * Ad-hoc solution. Force run consumers after category save inside test-case
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterExecute(
        Rows $subject,
        Rows $result,
        array $entityIds = [],
        $useTempTable = false
    ): Rows {
        $result = parent::afterExecute($subject, $result, $entityIds, $useTempTable);

        $objectManager = Bootstrap::getObjectManager();
        /** @var ConsumerInvoker $consumerInvoker */
        $consumerInvoker = $objectManager->get(ConsumerInvoker::class);
        $consumerInvoker->invoke(true);

        return $result;
    }
}
