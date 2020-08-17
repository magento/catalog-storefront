<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

/**
 * Changed entities object interface
 */
interface ChangedEntitiesInterface
{
    /**
     * Set changed entities metadata
     *
     * @param \Magento\CatalogExport\Model\Data\MetaInterface $meta
     * @return void
     */
    public function setMeta(MetaInterface $meta): void;

    /**
     * Get changed entities metadata
     *
     * @return \Magento\CatalogExport\Model\Data\MetaInterface
     */
    public function getMeta(): MetaInterface;

    /**
     * Set changed entities data
     *
     * @param \Magento\CatalogExport\Model\Data\DataInterface $data
     * @return void
     */
    public function setData(DataInterface $data): void;

    /**
     * Get changed entities data
     *
     * @return \Magento\CatalogExport\Model\Data\DataInterface
     */
    public function getData(): DataInterface;
}
