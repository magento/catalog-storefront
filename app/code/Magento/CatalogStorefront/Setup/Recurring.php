<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Temporary solution to configure connection for data storage service.
 *
 * @see \Magento\CatalogStorefront\Model\Storage\Client\ConnectionPull
 *
 * TODO: MC-29894
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader
     */
    private $configReader;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer
     */
    private $configWriter;

    /**
     * @param \Magento\Framework\App\DeploymentConfig\Reader $configReader
     * @param \Magento\Framework\App\DeploymentConfig\Writer $configWriter
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig\Reader $configReader,
        \Magento\Framework\App\DeploymentConfig\Writer $configWriter
    ) {
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $config = $this->configReader->load(\Magento\Framework\Config\File\ConfigFilePool::APP_ENV);
        $config['catalog-store-front'] = [
            'connections' => [
                'default' => [
                    'protocol' => 'http',
                    'hostname' => 'elasticsearch',
                    'port' => '9200',
                    'username' => '',
                    'password' => '',
                    'timeout' => 3,
                ]
            ],
            'timeout' => 60,
            'alias_name' => 'catalog_storefront',
            'source_prefix' => 'catalog_storefront_v',
            'source_current_version' => 1,
        ];
        $this->configWriter->saveConfig([\Magento\Framework\Config\File\ConfigFilePool::APP_ENV => $config], true);
    }
}
