<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Api\Data;

interface EntitiesRequestInterface
{
    /**
     * Get requested entities request data.
     *
     * @return \Magento\CatalogExport\Api\Data\EntityRequestDataInterface[]
     */
    public function getEntitiesRequestData(): array;

    /**
     * Set requested entities request data.
     *
     * @param \Magento\CatalogExport\Api\Data\EntityRequestDataInterface[] $data
     *
     * @return void
     */
    public function setEntitiesRequestData(array $data): void;

    /**
     * Get requested entities store view codes.
     *
     * @return string[]
     */
    public function getStoreViewCodes(): array;

    /**
     * Set requested entities store view codes.
     *
     * @param string[] $storeViewCodes
     *
     * @return void
     */
    public function setStoreViewCodes(array $storeViewCodes): void;
}
