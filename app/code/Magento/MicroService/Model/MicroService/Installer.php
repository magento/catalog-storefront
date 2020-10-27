<?php

namespace Magento\MicroService\Model\MicroService;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Stdlib\DateTime;
use Magento\Setup\Model\ConfigGenerator;

class Installer
{
    /**
     * Configuration for AMQP
     */
    const AMQP_HOST = 'host';
    const AMQP_PORT = 'port';
    const AMQP_USER = 'user';
    const AMQP_PASSWORD = 'password';
    /**
     * Configuration for Elasticsea
     */
    const ELASTICSEARCH_HOST = 'elasticsearch_server_hostname';
    const ELASTICSEARCH_ENGINE = 'engine';
    const ELASTICSEARCH_PORT = 'elasticsearch_server_port';
    const ELASTICSEARCH_INDEX_PREFIX = 'elasticsearch_index_prefix';
    /**
     * Other settings
     */
    const BASE_URL = 'backoffice-base-url';

    /**
     * @var Writer
     */
    private $deploymentConfigWriter;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ConfigGenerator
     */
    private $configGenerator;

    /**
     * Installer constructor.
     * @param Writer $deploymentConfigWriter
     * @param DateTime $dateTime
     * @param ConfigGenerator $configGenerator
     */
    public function __construct(
        Writer $deploymentConfigWriter,
        DateTime $dateTime,
        ConfigGenerator $configGenerator
    ) {
        $this->deploymentConfigWriter = $deploymentConfigWriter;
        $this->dateTime = $dateTime;
        $this->configGenerator = $configGenerator;
    }

    /**
     * Prepare cache list
     *
     * @return array
     */
    private function getCacheTypes(): array
    {
        return [
            'config' => 1,
            'compiled_config' => 1
        ];
    }

    /**
     * Create env.php file configuration
     *
     * @param array $parameters
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function install(array $parameters): void
    {
        $config = [
            'app_env' => [
                'cache_types' => $this->getCacheTypes(),
                'queue' => [
                    'consumers_wait_for_messages' => 0,
                    'amqp' => [
                        self::AMQP_HOST => $parameters[self::AMQP_HOST],
                        self::AMQP_USER => $parameters[self::AMQP_USER],
                        self::AMQP_PASSWORD => $parameters[self::AMQP_PASSWORD],
                        self::AMQP_PORT => $parameters[self::AMQP_PORT],
                    ]
                ],
                'system' => [
                    'default' => [
                        'catalog' => [
                            'search' => [
                                self::ELASTICSEARCH_PORT => $parameters[self::ELASTICSEARCH_PORT],
                                self::ELASTICSEARCH_INDEX_PREFIX => $parameters[self::ELASTICSEARCH_INDEX_PREFIX],
                                self::ELASTICSEARCH_HOST => $parameters[self::ELASTICSEARCH_HOST],
                                self::ELASTICSEARCH_ENGINE => $parameters[self::ELASTICSEARCH_ENGINE],
                            ]
                        ],
                        'backoffice' => [
                            'web' => [
                                'base_url' => $parameters[self::BASE_URL]
                            ]
                        ]
                    ]
                ],
                'install' => [
                    'date' => $this->dateTime->formatDate(true)
                ]
            ]
        ];

        $config['app_env'] = array_replace_recursive(
            $config['app_env'],
            $this->configGenerator->createCryptConfig([])->getData(),
            $this->configGenerator->createModeConfig()->getData()
        );

        $this->deploymentConfigWriter->saveConfig($config);
    }
}
