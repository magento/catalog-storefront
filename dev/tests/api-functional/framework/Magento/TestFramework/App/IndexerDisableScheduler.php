<?php
/**
 * Application configuration object. Used to access configuration when application is installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\App;

use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @inheritdoc
 */
class IndexerDisableScheduler
{
    /**
     * Handler for 'startTest' event
     *
     * @param TestCase $test
     * @return void
     */
    public function startTest(TestCase $test)
    {
        $indexer =  \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(Indexer::class);
        $indexer->load('catalog_data_exporter_products');
        $indexer->setScheduled(false);
    }
}
