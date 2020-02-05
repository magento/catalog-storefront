<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client\Config;

use Magento\Framework\Exception\NotFoundException;

/**
 * Entity config pool.
 *
 * Pool of all existing entity configs.
 *
 * @see EntityConfigInterface to understand what's the Entity Config is.
 */
class EntityConfigPool
{
    /**
     * @var EntityConfigInterface[]
     */
    private $configs;

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * Get config
     *
     * @param string $entityName
     * @return EntityConfigInterface
     * @throws NotFoundException
     */
    public function getConfig(string $entityName): EntityConfigInterface
    {
        if (!isset($this->configs[$entityName])) {
            throw new NotFoundException(
                __("'$entityName' storage entity type config not found.")
            );
        }

        return $this->configs[$entityName];
    }
}
