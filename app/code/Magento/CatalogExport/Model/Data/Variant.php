<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model\Data;

use Magento\CatalogExportApi\Api\Data\VariantInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Variant entity.
 */
class Variant extends AbstractModel implements VariantInterface
{
    private const ID = 'id';
    private const PARENT_ID = 'parent_id';
    private const PRODUCT_ID = 'product_id';
    private const DEFAULT_QTY = 'default_qty';
    private const QTY_MUTABILITY = 'qty_mutability';
    private const OPTION_VALUE_IDS = 'option_value_ids';
    private const PRICE = 'price';

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): string
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getProductId(): string
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultQty(): float
    {
        return $this->getData(self::DEFAULT_QTY);
    }

    /**
     * @inheritDoc
     */
    public function getQtyMutability(): bool
    {
        return $this->getData(self::QTY_MUTABILITY);
    }

    /**
     * @inheritDoc
     */
    public function getOptionValueIds(): array
    {
        return $this->getData(self::OPTION_VALUE_IDS);
    }

    /**
     * @inheritDoc
     */
    public function getPrice(): float
    {
        return $this->getData(self::PRICE);
    }
}
