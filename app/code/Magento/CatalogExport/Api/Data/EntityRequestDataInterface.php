<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Api\Data;

/**
 * Entity request data object interface
 */
interface EntityRequestDataInterface
{
    /**
     * Get requested entity id.
     *
     * @return int
     */
    public function getEntityId(): int;

    /**
     * Set requested entity id.
     *
     * @param int $entityId
     *
     * @return void
     */
    public function setEntityId(int $entityId): void;

    /**
     * Get requested entity attribute codes.
     *
     * @return string[]
     */
    public function getAttributeCodes(): array;

    /**
     * Set requested entity attribute codes.
     *
     * @param string[] $codes
     *
     * @return void
     */
    public function setAttributeCodes(array $codes): void;
}
