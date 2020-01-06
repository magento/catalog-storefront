<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Client\Config;

/**
 * Config of entity.
 */
interface EntityConfigInterface
{
    /**
     * Get DDL settings.
     *
     * @return array
     */
    public function getSettings(): array;
}
