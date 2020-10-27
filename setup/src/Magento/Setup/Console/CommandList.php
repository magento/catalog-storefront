<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console;

use Laminas\ServiceManager\ServiceManager;

/**
 * Class CommandList contains predefined list of commands for Setup.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommandList
{
    /**
     * Service Manager
     *
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * Constructor
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Gets list of setup command classes
     *
     * @return string[]
     */
    protected function getCommandsClasses()
    {
        return [
            \Magento\Setup\Console\Command\ConfigSetCommand::class,
            \Magento\Setup\Console\Command\DependenciesShowFrameworkCommand::class,
            \Magento\Setup\Console\Command\DependenciesShowModulesCircularCommand::class,
            \Magento\Setup\Console\Command\DependenciesShowModulesCommand::class,
            \Magento\Setup\Console\Command\DiCompileCommand::class,
            \Magento\Setup\Console\Command\ModuleEnableCommand::class,
            \Magento\Setup\Console\Command\ModuleDisableCommand::class,
            \Magento\Setup\Console\Command\ModuleStatusCommand::class,
            \Magento\Setup\Console\Command\ModuleUninstallCommand::class,
            \Magento\Setup\Console\Command\ModuleConfigStatusCommand::class,
            \Magento\Setup\Console\Command\MaintenanceAllowIpsCommand::class,
            \Magento\Setup\Console\Command\MaintenanceDisableCommand::class,
            \Magento\Setup\Console\Command\MaintenanceEnableCommand::class,
            \Magento\Setup\Console\Command\MaintenanceStatusCommand::class,
        ];
    }

    /**
     * Gets list of command instances.
     *
     * @return \Symfony\Component\Console\Command\Command[]
     * @throws \Exception
     */
    public function getCommands()
    {
        $commands = [];

        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->serviceManager->get($class);
            } else {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception('Class ' . $class . ' does not exist');
            }
        }

        return $commands;
    }
}
