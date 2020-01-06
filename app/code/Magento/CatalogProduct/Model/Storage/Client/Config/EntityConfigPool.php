<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Client\Config;

use Magento\Framework\Exception\NotFoundException;

/**
 * Entity config pool.
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
     */
    public function getConfig(string $entityName): EntityConfigInterface
    {
        if (!isset($this->configs[$entityName])) {
            throw new NotFoundException(
                __("'$entityName' type config not found.")
            );
        }

        return $this->configs[$entityName];
    }
}
