<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;

/**
 * Storage client DDL operations interface.
 *
 * This interface responsible for communication with data storage service in terms of DDL operations behind Catalog
 * Product Storefront App service.
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
interface DataDefinitionInterface
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
     * Check a data source in storage for existing.
     *
     * @param string $name
     * @return bool
     */
    public function existsDataSource($name);

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
}
