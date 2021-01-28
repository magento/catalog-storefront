<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogStorefrontConfig\Model;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\DateTime;

class Installer
{
    /**
     * Configuration for Search Service DB connection
     */
    const DB_HOST = 'magento-db-host';
    const DB_NAME = 'magento-db-name';
    const DB_USER = 'magento-db-user';
    const DB_PASSWORD = 'magento-db-password';
    const DB_TABLE_PREFIX = 'magento-db-table-prefix';

    /**
     * Configuration for Search Service ElasticSearch
     */
    const ES_ENGINE = 'magento-es-engine';
    const ES_HOSTNAME = 'magento-es-hostname';
    const ES_PORT = 'magento-es-port';
    const ES_INDEX_PREFIX = 'magento-es-index-prefix';
    const ES_USERNAME = 'magento-es-username';
    const ES_PASSWORD = 'magento-es-password';

    /**
     * Enable cache config value
     */
    private const CACHE_ENABLED = 1;

    /**
     * @var Writer
     */
    private $deploymentConfigWriter;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ModulesCollector
     */
    private $modulesCollector;

    /**
     * @param Writer $deploymentConfigWriter
     * @param DateTime $dateTime
     * @param ModulesCollector $modulesCollector
     */
    public function __construct(
        Writer $deploymentConfigWriter,
        DateTime $dateTime,
        ModulesCollector $modulesCollector
    ) {
        $this->deploymentConfigWriter = $deploymentConfigWriter;
        $this->dateTime = $dateTime;
        $this->modulesCollector = $modulesCollector;
    }

    /**
     * Create env.php file configuration
     *
     * @param array $optional
     * @throws FileSystemException
     *
     * @deprecated Later we will use another approach without dependency on Magento DB
     */
    public function install(array $optional): void
    {
        $config = [
            'app_env' => [
                'install' => [
                    'date' => $this->dateTime->formatDate(true)
                ],
                'resource' => [
                    'default_setup' => [
                        'connection' => 'default'
                    ]
                ],
                'db' => [
                    'connection' => [
                        'default' => [
                            'host' => $optional[self::DB_HOST],
                            'dbname' => $optional[self::DB_NAME],
                            'username' => $optional[self::DB_USER],
                            'password' => $optional[self::DB_PASSWORD],
                            'model' => 'mysql4',
                            'engine' => 'innodb',
                            'initStatements' => 'SET NAMES utf8;',
                            'active' => '1',
                            'driver_options' => [
                                1014 => false
                            ]
                        ]
                    ],
                    'table_prefix' => $optional[self::DB_TABLE_PREFIX]
                ],
                'storefront-catalog' => [
                    'connections' => [
                        //Connection config to monolith ES
                        'magento' => [
                            'protocol' => 'http',
                            'hostname' => $optional[self::ES_HOSTNAME],
                            'port' => $optional[self::ES_PORT],
                            'enable_auth' => $optional[self::ES_USERNAME] !== '',
                            'username' => $optional[self::ES_USERNAME],
                            'password' => $optional[self::ES_PASSWORD],
                            'timeout' => 30,
                            'engine' => $optional[self::ES_ENGINE],
                            'index_prefix' => $optional[self::ES_INDEX_PREFIX]
                        ],
                        //TODO Connection config to local ES
                        'local' => []
                    ]
                ],
                'cache_types' => [
                    'config' => self::CACHE_ENABLED,
                    'reflection' => self::CACHE_ENABLED,
                    'db_ddl' => self::CACHE_ENABLED,
                    'compiled_config' => self::CACHE_ENABLED,
                ],
                'MAGE_MODE' => 'developer'
            ],
            'app_config' => [
                'modules' => $this->modulesCollector->execute()
            ]
        ];

        $this->deploymentConfigWriter->saveConfig($config);
    }
}
