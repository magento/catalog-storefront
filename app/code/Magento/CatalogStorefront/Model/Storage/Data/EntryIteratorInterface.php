<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Data;

/**
 * The iterator of Storage Entry items.
 */
interface EntryIteratorInterface extends \Iterator
{
    /**
     * Get current entry from the iterator.
     *
     * @return EntryInterface
     */
    public function current(): EntryInterface;

    /**
     * Convert data to array.
     *
     * @param bool $sortById
     * @return array
     */
    public function toArray(bool $sortById = true): array;
}
