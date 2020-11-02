<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client;

use Magento\Framework\Exception\BulkException;

/**
 * Storage client interface for Write access operations.
 */
interface CommandInterface
{
    /**
     * Performs bulk insert.
     *
     * Any command operations SHOULD work through data source endpoint to avoid degradation of performance of query
     * operations. Instead of data source you can access alias as well and perform command operations on them, but
     * in this case significant insert of data for particular data source could influence of query operation
     * performance toward the same data source.
     *
     * Recommended way to perform bulk insert is:
     * 1. Create new data source.
     * 2. Fulfill the newly created data source by data.
     * 3. Switch link of alias from old data source to new one.
     * 4. Delete/archive the old data source.
     *
     * @param string $dataSourceName
     * @param string $entityName
     * @param array $entries
     * @return void
     * @throws BulkException
     */
    public function bulkInsert(string $dataSourceName, string $entityName, array $entries): void;

    /**
     * Performs bulk update operation.
     *
     * @param string $dataSourceName
     * @param string $entityName
     * @param array $entries
     *
     * @return void
     *
     * @throws BulkException
     */
    public function bulkUpdate(string $dataSourceName, string $entityName, array $entries): void;

    /**
     * Performs bulk delete.
     *
     * @param string $dataSourceName
     * @param string $entityName
     * @param array $ids
     * @return void
     */
    public function bulkDelete(string $dataSourceName, string $entityName, array $ids): void;

    /**
     * Performs delete by query.
     *
     * @param string $dataSourceName
     * @param string $entityName
     * @param array $entries
     * @throws \RuntimeException
     */
    public function deleteByQuery(string $dataSourceName, string $entityName, array $entries): void;
}
