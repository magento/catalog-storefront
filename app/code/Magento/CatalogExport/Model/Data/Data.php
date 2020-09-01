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
     * @var Entity[]
     */
    private $entities;

    /**
     * @inheritdoc
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @inheritdoc
     */
    public function setEntities(array $entities): void
    {
        $this->entities = $entities;
    }
}
