<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * State represents the current metadata information of Storage.
 */
class State
{
    /**
     * @var Reader
     */
    private $configReader;

    /**
     * @param Reader $configReader
     */
    public function __construct(Reader $configReader)
    {
        $this->configReader = $configReader;
    }

    /**
     * Get current alias name of storage.
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getAliasName(): string
    {
        $config = $this->configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        return $config['alias_name'];
    }

    /**
     * Get current data source name of storage taking into account version of the data source.
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getCurrentDataSourceName(): string
    {
        $config = $this->configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        return $config['source_prefix'] . $config['source_current_version'];
    }

    /**
     * Generate the new version of name for data source based on the current state.
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function generateNewDataSourceName(): string
    {
        $config = $this->configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        return $config['source_prefix'] . ++$config['source_current_version'];
    }
}
