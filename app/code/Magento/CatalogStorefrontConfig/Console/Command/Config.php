<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogStorefrontConfig\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogStorefrontConfig\Model\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for search service minimum config set up
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config extends Command
{
    /**
     * Command name
     *
     * @var string
     */
    private const COMMAND_NAME = 'storefront:catalog:init';

    /**
     * @var Installer
     */
    private $installer;

    /**
     * Installer constructor.
     *
     * @param Installer $installer
     */
    public function __construct(
        Installer $installer
    ) {
        parent::__construct();
        $this->installer = $installer;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(
                'Adds minimum required config data to env.php'
            )
            ->setDefinition($this->getOptionsList());

        parent::configure();
    }

    /**
     * @inheritDoc
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkOptions($input->getOptions());
        try {
            $this->installer->install(
                $input->getOptions()
            );
        } catch (\Throwable $exception) {
            $output->writeln('Installation failed: ' . $exception->getMessage());
            return Cli::RETURN_FAILURE;
        }
        $output->writeln('Installation complete');

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Provides options for command config
     *
     * @return array
     */
    private function getOptionsList()
    {
        return [
            new InputOption(
                Installer::ES_ENGINE,
                null,
                InputOption::VALUE_REQUIRED,
                'Elasticsearch engine'
            ),
            new InputOption(
                Installer::ES_HOSTNAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Elasticsearch hostname'
            ),
            new InputOption(
                Installer::ES_PORT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Elasticsearch port',
                '9200'
            ),
            new InputOption(
                Installer::ES_USERNAME,
                null,
                InputOption::VALUE_OPTIONAL,
                'Elasticsearch user name',
                ''
            ),
            new InputOption(
                Installer::ES_PASSWORD,
                null,
                InputOption::VALUE_OPTIONAL,
                'Elasticsearch user password',
                ''
            ),
            new InputOption(
                Installer::ES_INDEX_PREFIX,
                null,
                InputOption::VALUE_REQUIRED,
                'Elasticsearch index prefix'
            )
        ];
    }

    /**
     * Checks if all options are set
     *
     * @param array $options
     * @return void
     * @throws LocalizedException
     */
    private function checkOptions($options)
    {
        $forgottenOptions = [];
        foreach ($options as $optionKey => $option) {
            if ($option === null) {
                $forgottenOptions[] = $optionKey;
            }
        }
        if (count($forgottenOptions) > 0) {
            throw new LocalizedException(
                __(
                    'Please provide next options: '.PHP_EOL.'%1',
                    implode(',' . PHP_EOL, $forgottenOptions)
                )
            );
        }
    }
}
