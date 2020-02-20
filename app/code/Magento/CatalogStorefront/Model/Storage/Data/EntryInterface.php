<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Data;

/**
 * Data Object interface that represent entry returned by Storage.
 */
interface EntryInterface
{
    /**
     * Get identifier of entry.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Get data of entry.
     *
     * By specifying $field argument will be retrieve only specific attribute from the entry.
     *
     * @param string $field
     * @return mixed
     */
    public function getData(string $field = '');
}
