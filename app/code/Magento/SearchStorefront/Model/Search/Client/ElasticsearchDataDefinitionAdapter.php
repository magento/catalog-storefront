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
 * Elasticsearch client DDL adapter.
 */
class ElasticsearchDataDefinitionAdapter implements DataDefinitionInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConnectionPull
     */
    private $connectionPull;

    /**
     * Initialize Elasticsearch Client
     *
     * @param Config $config
     * @param ConnectionPull $connectionPull
     */
    public function __construct(
        Config $config,
        ConnectionPull $connectionPull
    ) {
        $this->config = $config;
        $this->connectionPull = $connectionPull;
    }

    /**
     * Get Elasticsearch connection.
     *
     * @return \Elasticsearch\Client
     */
    private function getConnection()
    {
        return $this->connectionPull->getConnection();
    }

    /**
     * @inheritdoc
     */
    public function createDataSource($name, $metadata)
    {
        try {
            $this->getConnection()->indices()->create(
                [
                    'index' => $name,
                    'body' => $metadata,
                ]
            );
        } catch (\Throwable $throwable) {
            throw new CouldNotSaveException(
                __("Error occurred while saving '$name' index."),
                $throwable
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteDataSource($name)
    {
        try {
            $this->getConnection()->indices()->delete(['index' => $name]);
        } catch (\Throwable $throwable) {
            throw new CouldNotDeleteException(
                __("Error occurred while deleting '$name' index."),
                $throwable
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function existsDataSource($name)
    {
        return $this->getConnection()->indices()->exists(['index' => $name]);
    }

    /**
     * @inheritdoc
     */
    public function createEntity(string $dataSourceName, string $entityName, array $schema)
    {
        $params = [
            'index' => $dataSourceName,
            // type is deprecated @see https://www.elastic.co/guide/en/elasticsearch/reference/6.1/removal-of-types.html
            'type' => $entityName,
            'body' => [
                $entityName => $this->config->getEntityConfig($entityName)->getSettings()
            ],
            'include_type_name' => true
        ];

        foreach ($schema as $field => $fieldInfo) {
            $params['body'][$entityName]['properties'][$field] = $fieldInfo;
        }

        try {
            $this->getConnection()->indices()->putMapping($params);
        } catch (\Throwable $throwable) {
            throw new CouldNotSaveException(
                __("Error occurred while saving '$entityName' entity in the '$dataSourceName' index."),
                $throwable
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function createAlias(string $aliasName, string $dataSourceName)
    {
        $params['body']['actions'] = [
            'add' => ['alias' => $aliasName, 'index' => $dataSourceName],
        ];

        try {
            $this->getConnection()->indices()->updateAliases($params);
        } catch (\Throwable $throwable) {
            throw new StateException(
                __("Error occurred while creating alias for '$dataSourceName' index."),
                $throwable
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function switchAlias(string $aliasName, string $oldDataSourceName, string $newDataSourceName)
    {
        $params['body']['actions'] = [
            'add' => ['alias' => $aliasName, 'index' => $newDataSourceName],
            'remove' => ['alias' => $aliasName, 'index' => $oldDataSourceName]
        ];

        try {
            $this->getConnection()->indices()->updateAliases($params);
        } catch (\Throwable $throwable) {
            throw new StateException(
                __(
                    "Error occurred while switching alias "
                    . "from '$oldDataSourceName' index to '$newDataSourceName' index."
                ),
                $throwable
            );
        }
    }
}
