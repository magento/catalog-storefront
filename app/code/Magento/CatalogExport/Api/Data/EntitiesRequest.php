<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Api\Data;

/**
 * Entities request object
 */
class EntitiesRequest implements EntitiesRequestInterface
{
    /**
     * @var EntityRequestDataInterface[]
     */
    private $entitiesRequestData;

    /**
     * @var string[]
     */
    private $storeViewCodes;

    /**
     * @inheritdoc
     */
    public function getEntitiesRequestData(): array
    {
        return $this->entitiesRequestData;
    }

    /**
     * @inheritdoc
     */
    public function setEntitiesRequestData(array $entitiesRequestData): void
    {
        $this->entitiesRequestData = $entitiesRequestData;
    }

    /**
     * @inheritdoc
     */
    public function getStoreViewCodes(): array
    {
        return $this->storeViewCodes;
    }

    /**
     * @inheritdoc
     */
    public function setStoreViewCodes(array $storeViewCodes): void
    {
        $this->storeViewCodes = $storeViewCodes;
    }
}
