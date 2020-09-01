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
     * Get entities array.
     *
     * @return \Magento\CatalogExport\Model\Data\Entity[];
     */
    public function getEntities(): array;

    /**
     * Set entities array.
     *
     * @param \Magento\CatalogExport\Model\Data\Entity[] $entities
     *
     * @return void
     */
    public function setEntities(array $entities): void;
}
