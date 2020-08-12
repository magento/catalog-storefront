<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Data;

/**
 * Data object interface for updated entities collector
 */
interface UpdatedEntitiesDataInterfaceV2
{
    /**
     * Set meta data
     *
     * @param array $meta
     * @return void
     */
    public function setMeta(array $meta): void;

    /**
     * Get meta data
     *
     * @return array
     */
    public function getMeta(): array;

    /**
     * Set data
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data): void;

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array;
}
