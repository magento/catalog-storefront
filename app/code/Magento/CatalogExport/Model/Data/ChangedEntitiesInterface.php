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
     * Get changed entities metadata
     *
     * @return \Magento\CatalogExport\Model\Data\MetaInterface
     */
    public function getMeta(): MetaInterface;

    /**
     * Set entities meta
     *
     * @param \Magento\CatalogExport\Model\Data\MetaInterface $meta
     *
     * @return void
     */
    public function setMeta(MetaInterface $meta): void;

    /**
     * Get changed entities data
     *
     * @return \Magento\CatalogExport\Model\Data\DataInterface
     */
    public function getData(): DataInterface;

    /**
     * Set entities data
     *
     * @param \Magento\CatalogExport\Model\Data\DataInterface $data
     *
     * @return void
     */
    public function setData(DataInterface $data): void;
}
