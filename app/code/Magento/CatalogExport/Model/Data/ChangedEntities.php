<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

/**
 * Changed entities object
 */
class ChangedEntities implements ChangedEntitiesInterface
{
    /**
     * @var DataInterface
     */
    private $data;

    /**
     * @var MetaInterface
     */
    private $meta;

    /**
     * @inheritdoc
     */
    public function setMeta(MetaInterface $meta): void
    {
        $this->meta = $meta;
    }

    /**
     * @inheritdoc
     */
    public function getMeta(): MetaInterface
    {
        return $this->meta;
    }

    /**
     * @inheritdoc
     */
    public function setData(DataInterface $data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function getData(): DataInterface
    {
        return $this->data;
    }
}
