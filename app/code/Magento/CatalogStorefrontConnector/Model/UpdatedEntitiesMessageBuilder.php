<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface;

/**
 * Reindex message builder
 */
class UpdatedEntitiesMessageBuilder
{
    /**
     * @var UpdatedEntitiesDataInterface
     */
    private $updatedEntitiesData;

    /**
     * @param UpdatedEntitiesDataInterface $updatedEntitiesData
     */
    public function __construct(
        UpdatedEntitiesDataInterface $updatedEntitiesData
    ) {
        $this->updatedEntitiesData = $updatedEntitiesData;
    }

    /**
     * Build message for storefront.catalog.*.update topic
     *
     * @param int $storeId
     * @param int[] $entityIds
     *
     * @return UpdatedEntitiesDataInterface
     */
    public function build(int $storeId, array $entityIds): UpdatedEntitiesDataInterface
    {
        $this->updatedEntitiesData->setStoreId($storeId);
        $this->updatedEntitiesData->setEntityIds($entityIds);

        return $this->updatedEntitiesData;
    }
}
