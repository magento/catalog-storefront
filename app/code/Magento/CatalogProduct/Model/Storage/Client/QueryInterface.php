<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Client;

use Magento\CatalogProduct\Model\Storage\Data\EntryInterface;
use Magento\CatalogProduct\Model\Storage\Data\EntryIteratorInterface;
use Magento\Framework\Exception\NotFoundException;

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
     * @param string $aliasName
     * @param string $entityName
     * @param int $id
     * @param array $fields
     * @return EntryInterface
     * @throws NotFoundException
     */
    public function getEntry(string $aliasName, string $entityName, int $id, array $fields): EntryInterface;

    /**
     * Access entries of Entity by array of unique identifier.
     *
     * $fields argument needs to specify array of fields that need to retrieve from document to avoid situation
     * of retrieving entire document that could badly influence on bandwidth, elapsed time and performance in general.
     *
     * Any query operations MUST work only through alias endpoint to avoid data integrity problems.
     *
     * @param string $aliasName
     * @param string $entityName
     * @param array $ids
     * @param array $fields
     * @return EntryIteratorInterface
     * @throws NotFoundException
     */
    public function getEntries(
        string $aliasName,
        string $entityName,
        array $ids,
        array $fields
    ): EntryIteratorInterface;

    /**
     * Access entry of Composite Entity by unique identifier.
     *
     * $fields argument needs to specify array of fields that need to retrieve from sub entries to avoid situation
     * of retrieving entire set of data that could badly influence on bandwidth, elapsed time and performance
     * in general.
     *
     * $subEntityFields is the similar to $fields argument but regulate scope of fields on nested level.
     *
     * Any query operations MUST work only through alias endpoint to avoid data integrity problems.
     *
     * @param string $aliasName
     * @param string $entityName
     * @param int $id
     * @param array $fields
     * @param array $subEntityFields
     * @return EntryInterface
     */
    public function getCompositeEntry(
        string $aliasName,
        string $entityName,
        int $id,
        array $fields,
        array $subEntityFields
    ): EntryInterface;

    /**
     * Access entries of Entity by array of unique identifier.
     *
     * $fields argument needs to specify array of fields that need to retrieve from document to avoid situation
     * of retrieving entire document that could badly influence on bandwidth, elapsed time and performance in general.
     *
     * $subEntityFields is the similar to $fields argument but regulate scope of fields on nested level.
     *
     * Any query operations MUST work only through alias endpoint to avoid data integrity problems.
     *
     * @param string $aliasName
     * @param string $entityName
     * @param array $ids
     * @param array $fields
     * @param array $subEntityFields
     * @return EntryIteratorInterface
     */
    public function getCompositeEntries(
        string $aliasName,
        string $entityName,
        array $ids,
        array $fields,
        array $subEntityFields
    ): EntryIteratorInterface;
}
