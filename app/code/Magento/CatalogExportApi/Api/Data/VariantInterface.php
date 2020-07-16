<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExportApi\Api\Data;

/**
 * Product variant data-object interface.
 */
interface VariantInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getParentId(): string;

    /**
     * @return string
     */
    public function getProductId(): string;

    /**
     * @return float
     */
    public function getDefaultQty(): float;

    /**
     * @return bool
     */
    public function getQtyMutability(): bool;

    /**
     * @return int[]
     */
    public function getOptionValueIds(): array;

    /**
     * @return float
     */
    public function getPrice(): float;
}
