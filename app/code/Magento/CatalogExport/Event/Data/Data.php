<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Event\Data;

/**
 * Data object for changed entities
 */
class Data
{
    /**
     * @var int[]
     */
    private $entityIds;

    /**
     * Get changed entities IDs
     *
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->entityIds;
    }

    /**
     * Set changed entities IDs
     *
     * @param array $ids
     * @return void
     */
    public function setIds(array $ids): void
    {
        $this->entityIds = $ids;
    }
}
