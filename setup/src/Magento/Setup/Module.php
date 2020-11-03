<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManager;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\DispatchableInterface;
use Magento\Framework\App\Response\HeaderProvider\XssProtection;
use Magento\Setup\Mvc\View\Http\InjectTemplateListener;

/**
 * Laminas module declaration
 */
class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface
{
    /**
     * @inheritDoc
     */
    public function onBootstrap(EventInterface $e)
    {
        //Do nothing here
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        // phpcs:disable
        $result = array_merge_recursive(
            include __DIR__ . '/../../../config/module.config.php',
            include __DIR__ . '/../../../config/di.config.php',
        );
        // phpcs:enable
        return $result;
    }
}
