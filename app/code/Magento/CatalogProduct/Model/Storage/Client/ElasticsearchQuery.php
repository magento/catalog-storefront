<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Client;

use Magento\CatalogProduct\Model\Storage\Data\DocumentFactory;
use Magento\CatalogProduct\Model\Storage\Data\DocumentIteratorFactory;
use Magento\CatalogProduct\Model\Storage\Data\EntryInterface;
use Magento\CatalogProduct\Model\Storage\Data\EntryIteratorInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Elasticsearch client adapter for read access operations.
 */
class ElasticsearchQuery implements QueryInterface
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
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var DocumentIteratorFactory
     */
    private $documentIteratorFactory;

    /**
     * Initialize Elasticsearch Client
     *
     * @param Config $config
     * @param ConnectionPull $connectionPull
     * @param DocumentFactory $documentFactory
     * @param DocumentIteratorFactory $documentIteratorFactory
     */
    public function __construct(
        Config $config,
        ConnectionPull $connectionPull,
        DocumentFactory $documentFactory,
        DocumentIteratorFactory $documentIteratorFactory
    ) {
        $this->config = $config;
        $this->documentFactory = $documentFactory;
        $this->documentIteratorFactory = $documentIteratorFactory;
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
    public function getEntry(string $indexName, string $entityName, int $id, array $fields): EntryInterface
    {
        $query = [
            'index' => $indexName,
            'type' => $entityName,
            'id' => $id,
            '_source' => $fields
        ];
        try {
            $result = $this->getConnection()->get($query);
        } catch (\Throwable $throwable) {
            throw new NotFoundException(
                __("'$entityName' type document with id '$id' not found in index '$indexName'."),
                $throwable
            );
        }

        return $this->documentFactory->create($result);
    }

    /**
     * @inheritdoc
     */
    public function getCompositeEntry(
        string $indexName,
        string $entityName,
        int $id,
        array $fields,
        array $subEntityFields
    ): EntryInterface {
        $query = [
            'index' => $indexName,
            'type' => $entityName,
            'body' => [
                'query' => ['term' => ['_id' => $id]],
                'aggs' => [
                    'nested_entries' => [
                        'children' => ['type' => $this->config->getEntityConfig($entityName)->getChildKey()],
                        'aggs' => [
                            'variants' => [
                                'top_hits' => [
                                    '_source' => [
                                        'includes' => array_merge(
                                            $subEntityFields,
                                            [$this->config->getEntityConfig($entityName)->getJoinField()]
                                        )
                                    ],
                                    'size' => $this->config->getEntityConfig($entityName)->getMaxChildren()
                                ]
                            ]
                        ]
                    ],
                ]
            ],
            '_source' => $fields
        ];

        try {
            $result = $this->getConnection()->search($query);
        } catch (\Throwable $throwable) {
            throw new NotFoundException(
                __("'$entityName' type document with id '$id' not found in index '$indexName'."),
                $throwable
            );
        }

        return $this->documentFactory->create($result);
    }

    /**
     * @inheritdoc
     */
    public function getEntries(string $indexName, string $entityName, array $ids, array $fields): EntryIteratorInterface
    {
        $query = [
            'index' => $indexName,
            'type' => $entityName,
            // ids must be provided as indices array to avoid error on request
            'body' => ['ids' => \array_values($ids)],
            '_source' => $fields
        ];
        try {
            $result = $this->getConnection()->mget($query);

            // TODO: MC-29513 handle error in $result['error']['root_cause'], e.g. index_not_found_exception
            if (isset($result['docs'][0]['error'])) {
                throw new NotFoundException(__('Error TBD'));
            }
        } catch (\Throwable $throwable) {
            throw new NotFoundException(
                __(
                    "Documents with ids '"
                    . json_encode($ids)
                    . "' not found in index '$indexName'."
                ),
                $throwable
            );
        }

        return $this->documentIteratorFactory->create($result);
    }

    /**
     * @inheritdoc
     */
    public function getCompositeEntries(
        string $indexName,
        string $entityName,
        array $ids,
        array $fields,
        array $subEntityFields
    ): EntryIteratorInterface {
        $query = [
            'index' => $indexName,
            'type' => $entityName,
            'body' => [
                'query' => ['terms' => ['_id' => $ids]],
                'aggs' => [
                    'nested_entries' => [
                        'children' => ['type' => $this->config->getEntityConfig($entityName)->getChildKey()],
                        'aggs' => [
                            'variants' => [
                                'top_hits' => [
                                    '_source' => [
                                        'includes' => array_merge(
                                            $subEntityFields,
                                            [$this->config->getEntityConfig($entityName)->getJoinField()]
                                        )
                                    ],
                                    'size' => $this->config->getEntityConfig($entityName)->getMaxChildren()
                                ]
                            ]
                        ]
                    ],
                ]
            ],
            '_source' => $fields
        ];

        try {
            $result = $this->getConnection()->search($query);
        } catch (\Throwable $throwable) {
            throw new NotFoundException(
                __(
                    "'$entityName' type documents with ids '"
                    . json_encode($ids)
                    . "' not found in index '$indexName'."
                ),
                $throwable
            );
        }

        return $this->documentIteratorFactory->create($result);
    }
}
