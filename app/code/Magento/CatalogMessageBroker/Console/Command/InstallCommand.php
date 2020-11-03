<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Console\Command;

use Magento\CatalogMessageBroker\Model\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to backup code base and user data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallCommand extends  Command
{
    /**
     * Configuration for AMQP
     */
    const AMQP_HOST = 'amqp-host';
    const AMQP_PORT = 'amqp-port';
    const AMQP_USER = 'amqp-user';
    const AMQP_PASSWORD = 'amqp-password';
    /**
     * Configuration for Elasticsea
     */
    const ELASTICSEARCH_ENGINE = 'elasticsearch-engine';

    /**
     * @var Installer
     */
    private $installer;

    /**
     * TopologyInstall constructor.
     * @param Installer $installer
     * @param string|null $name
     */
    public function __construct(
        Installer $installer,
        string $name = null
    ) {
        parent::__construct($name);
        $this->installer = $installer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('catalog:message-broker:install')
            ->setDescription('Install catalog message broker')
            ->setDefinition($this->getOptionsList());

        parent::configure();
    }

    /**
     * Prepare options list
     *
     * @param int $mode
     * @return InputOption[]
     */
    public function getOptionsList($mode = InputOption::VALUE_REQUIRED)
    {
        return [
            new InputOption(
                self::AMQP_HOST,
                null,
                $mode,
                'AMQP host'
            ),
            new InputOption(
                self::AMQP_PORT,
                null,
                $mode,
                'AMQP port'
            ),
            new InputOption(
                self::AMQP_PASSWORD,
                null,
                $mode,
                'AMQP password'
            ),
            new InputOption(
                self::AMQP_USER,
                null,
                $mode,
                'AMQP user'
            ),
            new InputOption(
                self::ELASTICSEARCH_ENGINE,
                null,
                $mode,
                'Elasticsearch engine'
            ),
            new InputOption(
                Installer::ELASTICSEARCH_HOST,
                null,
                $mode,
                'Elasticsearch host'
            ),
            new InputOption(
                Installer::ELASTICSEARCH_INDEX_PREFIX,
                null,
                $mode,
                'Elasticsearch index prefix'
            ),
            new InputOption(
                Installer::ELASTICSEARCH_PORT,
                null,
                $mode,
                'Elasticsearch port'
            ),
            new InputOption(
                Installer::BASE_URL,
                null,
                $mode,
                'Base URL'
            ),
        ];
    }

    /**
     * Map options
     *
     * @param array $options
     * @return array
     */
    private function mapOptions(array $options): array
    {
        $options[Installer::ELASTICSEARCH_ENGINE] = $options[self::ELASTICSEARCH_ENGINE];
        $options[Installer::AMQP_PORT] = $options[self::AMQP_PORT];
        $options[Installer::AMQP_HOST] = $options[self::AMQP_HOST];
        $options[Installer::AMQP_USER] = $options[self::AMQP_USER];
        $options[Installer::AMQP_PASSWORD] = $options[self::AMQP_PASSWORD];
        return $options;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->installer->install(
            $this->mapOptions($input->getOptions())
        );
    }
}
