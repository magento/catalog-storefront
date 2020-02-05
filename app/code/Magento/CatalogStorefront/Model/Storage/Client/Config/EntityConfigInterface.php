<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client\Config;

/**
 * Config of entity.
 *
 * This config represents the DDL property of particular Storage entity (table/doc type/etc...)
 * As the example of such configuration should be:
 *      1. schema of table or mapping of document type;
 *      2. indexes of table;
 *      3. foreign keys;
 *      ...
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
