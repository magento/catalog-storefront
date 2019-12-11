<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Config\File\ConfigFilePool;


class State
{
    /**
     * @var Reader
     */
    private $configReader;

    public function __construct(Reader $configReader) {
        $this->configReader = $configReader;
    }

    public function getAliasName()
    {
        $config = $this->configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        return $config['alias_name'];
    }

    public function getCurrentDataSourceName()
    {
        $config = $this->configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        return $config['source_prefix'] . $config['source_current_version'];
    }

    public function generateNewDataSourceName()
    {
        $config = $this->configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        return $config['source_prefix'] . ++$config['source_current_version'];
    }
}
