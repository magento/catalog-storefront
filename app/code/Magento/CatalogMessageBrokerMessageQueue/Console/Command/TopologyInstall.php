<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBrokerMessageQueue\Console\Command;

use Magento\Framework\Amqp\TopologyInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to backup code base and user data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TopologyInstall extends  Command
{
    /**
     * @var TopologyInstaller
     */
    private $topologyInstaller;

    /**
     * TopologyInstall constructor.
     * @param TopologyInstaller $topologyInstaller
     * @param string|null $name
     */
    public function __construct(
        TopologyInstaller $topologyInstaller,
        string $name = null
    ) {
        parent::__construct($name);
        $this->topologyInstaller = $topologyInstaller;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('topology:install')
            ->setDescription('Install topology');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->topologyInstaller->install();
    }
}
