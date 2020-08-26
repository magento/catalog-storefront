<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

/**
 * Data object interface for changed entities
 */
interface DataInterface
{
    /**
     * Get changed entities IDs
     *
     * @return int[]
     */
    public function getIds(): array;
}
