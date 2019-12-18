<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Client;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Connection pull.
 *
 * Temporal solution to setup connection for storage while catalog storefront application is a part of Magento monolith.
 * This Class contains a few hard-codes and rely on a strict and "hidden" structure of connection configuration.
 *
 * For now connection is setup during the installation of Magento application
 * @see \Magento\CatalogProduct\Setup\Recurring
 *
 * Example that we hard-code during installation of Magento:
 * 'catalog-store-front' => [
 *     'connections' => [
 *         'default' => [
 *             'protocol' => 'http',
 *             'hostname' => 'localhost',
 *             'port' => '9200',
 *             'username' => '',
 *             'password' => '',
 *             'timeout' => 3
 *         ]
 *     ],
 *     'timeout' => 60,
 *     'alias_name' => 'catalog_storefront',
 *     'source_prefix' => 'catalog_storefront_v',
 *     'source_current_version' => 1
 * ]
 *
 * For now you only has to follow the structure and modify value of each option to appropriate that correspond to
 * your environment. It's possible by particularly modify the app/etc/env.php file that is the representation
 * of connection configuration in the Magento application.
 *
 * TODO: MC-29894
 */
class ConnectionPull
{
    /**
     * @var \Elasticsearch\Client[]
     */
    private $connectionPull;

    /**
     * @var array
     */
    private $clientOptions;

    /**
     * Initialize Elasticsearch Client
     *
     * @param Reader $configReader
     * @throws ConfigurationMismatchException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function __construct(Reader $configReader)
    {
        $configData = $configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        $options = $configData['connections']['default'];

        if (empty($options['hostname']) || ((!empty($options['enableAuth'])
                    && ($options['enableAuth'] == 1)) && (empty($options['username']) || empty($options['password'])))
        ) {
            throw new ConfigurationMismatchException(
                __('The search failed because of a search engine misconfiguration.')
            );
        }
        $this->clientOptions = $options;
    }

    /**
     * Get Elasticsearch connection.
     *
     * @return \Elasticsearch\Client
     */
    public function getConnection()
    {
        $pid = getmypid();
        if (!isset($this->client[$pid])) {
            $config = $this->buildConfig($this->clientOptions);
            $this->connectionPull[$pid] = \Elasticsearch\ClientBuilder::fromConfig($config, true);
        }
        return $this->connectionPull[$pid];
    }

    /**
     * Build config.
     *
     * @param array $options
     * @return array
     */
    private function buildConfig($options = [])
    {
        $portString = '';
        if (!empty($options['port'])) {
            $portString = ':' . $options['port'];
        }

        $host = $options['protocol'] . '://' . $options['hostname'] . $portString;

        $options['hosts'] = [$host];

        return $options;
    }
}
