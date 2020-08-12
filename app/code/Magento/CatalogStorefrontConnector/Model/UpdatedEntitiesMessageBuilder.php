<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface;
use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterfaceV2;

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
     * @var UpdatedEntitiesDataInterfaceV2
     */
    private $updatedEntitiesDataV2;

    /**
     * @param UpdatedEntitiesDataInterface $updatedEntitiesData
     * @param UpdatedEntitiesDataInterfaceV2 $updatedEntitiesDataV2
     */
    public function __construct(
        UpdatedEntitiesDataInterface $updatedEntitiesData,
        UpdatedEntitiesDataInterfaceV2 $updatedEntitiesDataV2

    ) {
        $this->updatedEntitiesData = $updatedEntitiesData;
        $this->updatedEntitiesDataV2 = $updatedEntitiesDataV2;
    }

    /**
     * todo: make param non-optional
     * Build message for storefront.catalog.*.update topic
     *
     * @param int|null $storeId
     * @param int[] $entityIds
     * @param string|null $eventType
     * @return UpdatedEntitiesDataInterface
     */
    public function build(array $entityIds, ?string $eventType = null, ?int $storeId = null): UpdatedEntitiesDataInterface
    {
        $this->updatedEntitiesData->setEntityIds($entityIds);
        $this->updatedEntitiesData->setEventType($eventType);
        $this->updatedEntitiesData->setStoreId($storeId);

        return $this->updatedEntitiesData;
    }

    /**
     * @param array $meta
     * @param array $data
     * @return UpdatedEntitiesDataInterfaceV2
     */
    public function buildv2(array $meta, array $data): UpdatedEntitiesDataInterfaceV2
    {
        $this->updatedEntitiesDataV2->setMeta($meta);
        $this->updatedEntitiesDataV2->setData($data);

        return $this->updatedEntitiesDataV2;
    }
}
