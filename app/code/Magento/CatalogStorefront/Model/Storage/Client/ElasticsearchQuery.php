<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client;

use Magento\CatalogStorefront\Model\Storage\Data\DocumentFactory;
use Magento\CatalogStorefront\Model\Storage\Data\DocumentIteratorFactory;
use Magento\CatalogStorefront\Model\Storage\Data\EntryInterface;
use Magento\CatalogStorefront\Model\Storage\Data\EntryIteratorInterface;
use Magento\CatalogStorefront\Model\Storage\Data\SearchResultIteratorFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;
use Psr\Log\LoggerInterface;

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
     * @var SearchResultIteratorFactory
     */
    private $searchResultIteratorFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initialize Elasticsearch Client
     *
     * @param Config $config
     * @param ConnectionPull $connectionPull
     * @param DocumentFactory $documentFactory
     * @param DocumentIteratorFactory $documentIteratorFactory
     * @param SearchResultIteratorFactory $searchResultIteratorFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        ConnectionPull $connectionPull,
        DocumentFactory $documentFactory,
        DocumentIteratorFactory $documentIteratorFactory,
        SearchResultIteratorFactory $searchResultIteratorFactory,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->documentFactory = $documentFactory;
        $this->documentIteratorFactory = $documentIteratorFactory;
        $this->searchResultIteratorFactory = $searchResultIteratorFactory;
        $this->connectionPull = $connectionPull;
        $this->logger = $logger;
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
            $result = $this->connectionPull->getConnection()->get($query);
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
    public function searchEntries(
        string $indexName,
        string $entityName,
        array $searchBody,
        ?int $size = null,
        ?int $pointer = null
    ): EntryIteratorInterface {
        $query = [
            'index' => $indexName,
            'type' => $entityName,
        ];

        foreach ($searchBody as $key => $value) {
            $query['body']['query']['bool']['must'][]['match'][$key] = $value;
        }

        if (null !== $size) {
            $query['body']['size'] = $size;
            $query['body']['sort'][] = ['_id' => 'asc'];
            $query['body']['search_after'] = [$pointer ?? 0];
        }

        try {
            $result = $this->connectionPull->getConnection()->search($query);
        } catch (\Throwable $throwable) {
            throw new RuntimeException(
                __("Storage error: {$throwable->getMessage()} Query was:" . \json_encode($query)),
                $throwable
            );
        }
        $this->checkErrors($result, $indexName);

        return $this->searchResultIteratorFactory->create($result);
    }

    /**
     * @inheritdoc
     */
    public function getEntriesCount(string $indexName, string $entityName, array $terms): int
    {
        $query = [
            'index' => $indexName,
            'type' => $entityName,
            'size' => 0,
        ];

        foreach ($terms as $key => $value) {
            $query['body']['aggs']['entries_count']['filter']['bool']['filter'][]['term'][$key] = $value;
        }

        try {
            $result = $this->connectionPull->getConnection()->search($query);
        } catch (\Throwable $throwable) {
            throw new RuntimeException(
                __("Storage error: {$throwable->getMessage()} Query was:" . \json_encode($query)),
                $throwable
            );
        }

        return $result['aggregations']['entries_count']['doc_count'] ?? 0;
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
            $result = $this->connectionPull->getConnection()->mget($query);
        } catch (\Throwable $throwable) {
            throw new RuntimeException(
                __(
                    "Storage error: {$throwable->getMessage()}
                Query was:" . json_encode($query)
                ),
                $throwable
            );
        }
        $this->checkErrors($result, $indexName);

        return $this->documentIteratorFactory->create($ids, $result);
    }

    /**
     * Handle the error occurrences of each returned document.
     *
     * @param array $result
     * @param string $indexName
     * @throws NotFoundException
     */
    private function checkErrors(array $result, string $indexName)
    {
        $errors = [];
        $notFound = [];
        if (isset($result['docs'])) {
            foreach ($result['docs'] as $doc) {
                if (isset($doc['error']) && !empty($doc['error'])) {
                    $errors [] = sprintf("Entity id: %d\nReason: %s", $doc['_id'], $doc['error']['reason']);
                } elseif (isset($doc['found']) && false === $doc['found']) {
                    $notFound[] = $doc['_id'];
                }
            }
        }

        if (!empty($errors)) {
            throw new NotFoundException(__("Index name: {$indexName}\nList of errors: '" . \implode(', ', $errors)));
        }
        if (!empty($notFound)) {
            $this->logger->notice(\sprintf('Items "%s" not found in index %s', \implode(', ', $notFound), $indexName));
        }
    }
}
