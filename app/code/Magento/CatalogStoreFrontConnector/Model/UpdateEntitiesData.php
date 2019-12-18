<?php


namespace Magento\CatalogStoreFrontConnector\Model;


class UpdateEntitiesData implements UpdateEntitiesDataInterface
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * @var int
     */
    private $entityId;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var array
     */
    private $entityData;

    /**
     * @param string $entityType
     * @return void
     */
    public function setEntityType(string $entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @param int $entityId
     * @return void
     */
    public function setEntityId(int $entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @param int $storeId
     *
     * @return void
     */
    public function setStoreId(int $storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Get store ID for products reindex
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->storeId;
    }

    /**
     * @param array $entityData
     * @return void
     */
    public function setEntityData(array $entityData)
    {
        $this->entityData = $entityData;
    }

    /**
     * @return array
     */
    public function getEntityData(): array
    {
        return $this->entityData;
    }
}