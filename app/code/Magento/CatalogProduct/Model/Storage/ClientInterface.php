<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage;

use Magento\CatalogProduct\Model\Storage\Data\EntryInterface;
use Magento\CatalogProduct\Model\Storage\Data\EntryIteratorInterface;
use Magento\Framework\Exception\BulkException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\StateException;

/**
 * Storage client interface.
 *
 * This interface responsible for communication with data storage service behind Catalog Product Storefront App service.
 *
 * Data Storage that this interface is represent has to support next data model:
 * {
 *      'Data source 1' => {
 *          'Entity 1' => {
 *              {id, entry data...},
 *              {id, entry data...},
 *              {id, entry data...},
 *              ...
 *          },
 *          'Entity 2' => {
 *              {id, entry data...},
 *              {id, entry data...},
 *              {id, entry data...},
 *              ....
 *          },
 *          ...
 *      },
 *      'Data source 2' => {...}
 * }
 *
 * Where are:
 *  1. 'Data source' conception is something like 'Database' in typical relational DB or 'Index' in DOC-oriented DB;
 *  2. 'Entity' could be interpreted as typical 'Table' in the terms of relational DB
 * or 'Entity type' in DOC-oriented DB;
 *  3. 'id' - is an identification of each entry. It has to be an unique key of the entry in the particular 'Entity'.
 *
 * Additionally, Storage has to support the meaning of aliases, or something similar to that to be able to handle
 * the next behavior:
 *  1. Access to single data sources by alias.
 *  2. Switch link of alias to different data source with minimum downtime.
 * The meaning of alias help continuously handle query operations in case of significant amount of command operations
 * or full update of data. Meanwhile, query operations access to 'Data source 1' by alias, we can fulfill
 * the 'Data source 2' by data in background. In the moment when 'Data source 2' would be ready to use we make a switch
 * of link for alias from 'Data source 1' to 'Data source 2'. After that switch query operations would access to
 * 'Data source 2' by the same alias.
 */
interface ClientInterface
{
    /**
     * Creates a data source in storage.
     *
     * Data source conception is something like 'Database' in typical relational DB or 'Index' in DOC-oriented DB.
     *
     * @param string $name
     * @param array $metadata
     * @return void
     * @throws CouldNotSaveException
     */
    public function createDataSource($name, $metadata);

    /**
     * Delete a source in storage.
     *
     * @param string $name
     * @return void
     * @throws CouldNotDeleteException
     */
    public function deleteDataSource($name);

    /**
     * Create Entity with schema definition.
     *
     * Entity could be interpreted as typical 'Table' in the terms of relational DB or 'Entity type' in DOC-oriented DB.
     *
     * @param string $dataSourceName
     * @param string $entityName
     * @param array $schema
     * @return void
     * @throws CouldNotSaveException
     */
    public function createEntity(string $dataSourceName, string $entityName, array $schema);

    /**
     * Create alias for data source.
     *
     * @param string $aliasName
     * @param string $dataSourceName
     * @return void
     * @throws StateException
     */
    public function createAlias(
        string $aliasName,
        string $dataSourceName
    );

    /**
     * Switch link of alias from one data source to another.
     *
     * @param string $aliasName
     * @param string $oldDataSourceName
     * @param string $newDataSourceName
     * @return void
     */
    public function switchAlias(
        string $aliasName,
        string $oldDataSourceName,
        string $newDataSourceName
    );

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
     * Access entry of Composite Entity by unique identifier.
     *
     * $fields argument needs to specify array of fields that need to retrieve from sub entries to avoid situation
     * of retrieving entire set of data that could badly influence on bandwidth, elapsed time and performance
     * in general.
     *
     * $subEntityFields
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
    public function bulkInsert(string $dataSourceName, string $entityName, array $entries);
}
