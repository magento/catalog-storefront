<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogStorefrontConfig\Model;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * This class is responsible for collecting all active modules
 */
class ModulesCollector
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Collect modules to form app/etc/config.php file
     *
     * @return array
     */
    public function execute(): array
    {
        $modules = [];
        foreach (array_keys($this->componentRegistrar->getPaths('module')) as $moduleKey) {
            $modules[$moduleKey] = 1;
        }

        return $modules;
    }
}
