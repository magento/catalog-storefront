<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

/**
 * Data object for changed entities
 */
class Data implements DataInterface
{
    /**
     * @var int[]
     */
    private $entityIds;

    /**
     * @inheritdoc
     */
    public function setEntityIds(array $entityIds): void
    {
        $this->entityIds = $entityIds;
    }

    /**
     * @inheritdoc
     */
    public function getEntityIds(): array
    {
        return $this->entityIds;
    }
}
