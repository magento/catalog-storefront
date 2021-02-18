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
     * Configuration for Search Service ElasticSearch
     */
    const ES_PROTOCOL = 'es-protocol';
    const ES_ENGINE = 'es-engine';
    const ES_HOSTNAME = 'es-hostname';
    const ES_PORT = 'es-port';
    const ES_INDEX_PREFIX = 'es-index-prefix';
    const ES_USERNAME = 'es-username';
    const ES_PASSWORD = 'es-password';

    const DEFAULT_PROTOCOL = 'http';

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
                'catalog-store-front' => [
                    'connections' => [
                        //Connection config to monolith ES
                        'default' => [
                            'protocol' => $optional[self::ES_PROTOCOL] ?? self::DEFAULT_PROTOCOL,
                            'hostname' => $optional[self::ES_HOSTNAME],
                            'port' => $optional[self::ES_PORT],
                            'enable_auth' => $optional[self::ES_USERNAME] !== '',
                            'username' => $optional[self::ES_USERNAME],
                            'password' => $optional[self::ES_PASSWORD],
                            'timeout' => 30,
                            'engine' => $optional[self::ES_ENGINE],
                        ],
                        //TODO Connection config to local ES
                        'local' => []
                    ],
                    'timeout' => 60,
                    'alias_name' => 'catalog_storefront',
                    'source_prefix' => 'catalog_storefront_v',
                    'source_current_version' => 1
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
