<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBrokerMessageQueue\Console;

use Magento\Framework\ObjectManagerInterface;
use Magento\CatalogMessageBrokerMessageQueue\Console\Command\CatalogMessageBrokerMessageQueueInstall;
use Magento\CatalogMessageBrokerMessageQueue\Console\Command\TopologyInstall;

/**
 * Class CommandList
 *
 * Provides list of commands to be available for application
 */
class CommandList implements \Magento\Framework\Console\CommandListInterface
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Gets list of command classes
     *
     * @return string[]
     */
    private function getCommandsClasses(): array
    {
        return [
            TopologyInstall::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getCommands()
    {
        $commands = [];
        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->objectManager->get($class);
            } else {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception('Class ' . $class . ' does not exist');
            }
        }
        return $commands;
    }
}
