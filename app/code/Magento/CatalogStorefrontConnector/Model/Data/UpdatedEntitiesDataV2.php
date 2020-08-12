<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Model\Data;

/**
 * Data object for updated entities collector
 */
class UpdatedEntitiesDataV2 implements UpdatedEntitiesDataInterfaceV2
{
    /**
     * todo: move these constants
     * Event types
     */
    const CATEGORIES_UPDATED_EVENT_TYPE = 'categories_updated';

    const CATEGORIES_DELETED_EVENT_TYPE = 'categories_deleted';

    const PRODUCTS_UPDATED_EVENT_TYPE = 'products_updated';

    const PRODUCTS_DELETED_EVENT_TYPE = 'products_deleted';

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $meta;

    /**
     * @inheritdoc
     */
    public function setMeta(array $meta): void
    {
        $this->meta = $meta;
    }

    /**
     * @inheritdoc
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @inheritdoc
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        return $this->data;
    }
}
