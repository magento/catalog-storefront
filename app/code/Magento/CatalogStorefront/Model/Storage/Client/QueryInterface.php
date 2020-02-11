<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client;

use Magento\CatalogStorefront\Model\Storage\Data\EntryInterface;
use Magento\CatalogStorefront\Model\Storage\Data\EntryIteratorInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Storage client interface for Read access operations.
 */
interface QueryInterface
{
    /**
     * Access entry of Entity by unique identifier.
     *
     * $fields argument needs to specify array of fields that need to retrieve from entry to avoid situation
     * of retrieving entire document that could badly influence on bandwidth, elapsed time and performance in general.
     *
     * Any query operations MUST work only through alias endpoint to avoid data integrity problems.
     *
     * @param string $indexName
     * @param string $entityName
     * @param int $id
     * @param array $fields
     * @return EntryInterface
     * @throws NotFoundException
     */
    public function getEntry(string $indexName, string $entityName, int $id, array $fields): EntryInterface;

    /**
     * Access entries of Entity by array of unique identifier.
     *
     * $fields argument needs to specify array of fields that need to retrieve from document to avoid situation
     * of retrieving entire document that could badly influence on bandwidth, elapsed time and performance in general.
     *
     * Any query operations MUST work only through alias endpoint to avoid data integrity problems.
     *
     * @param string $indexName
     * @param string $entityName
     * @param array $ids
     * @param array $fields
     * @return EntryIteratorInterface
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function getEntries(
        string $indexName,
        string $entityName,
        array $ids,
        array $fields
    ): EntryIteratorInterface;
}
