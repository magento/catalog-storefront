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
    //TODO: Add pagination and remove max size search size https://github.com/magento/catalog-storefront/issues/418
    private const SEARCH_LIMIT = 5000;

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
     * @param ConnectionPull $connectionPull
     * @param DocumentFactory $documentFactory
     * @param DocumentIteratorFactory $documentIteratorFactory
     * @param SearchResultIteratorFactory $searchResultIteratorFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectionPull $connectionPull,
        DocumentFactory $documentFactory,
        DocumentIteratorFactory $documentIteratorFactory,
        SearchResultIteratorFactory $searchResultIteratorFactory,
        LoggerInterface $logger
    ) {
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
    public function searchMatchedEntries(
        string $indexName,
        string $entityName,
        array $searchBody,
        ?string $queryContext = 'must'
    ): EntryIteratorInterface {
        $searchQuery = $this->buildSearchQuery($searchBody, $queryContext, 'match');
        $searchResult = $this->searchEntries($indexName, $entityName, $searchQuery);
        return $this->searchResultIteratorFactory->create($searchResult);
    }

    /**
     * @inheritdoc
     */
    public function searchFilteredEntries(
        string $indexName,
        string $entityName,
        array $searchBody,
        ?string $clauseType = 'terms'
    ): EntryIteratorInterface {
        $searchQuery = $this->buildSearchQuery($searchBody, 'filter', $clauseType);
        $searchResult = $this->searchEntries($indexName, $entityName, $searchQuery);
        return $this->searchResultIteratorFactory->create($searchResult);
    }

    /**
     * @inheritdoc
     */
    public function searchAggregatedFilteredEntries(
        string $indexName,
        string $entityName,
        array $searchBody,
        string $aggregateField,
        int $minDocCount,
        ?string $clauseType = 'terms'
    ): array {
        $searchQuery = $this->buildSearchQuery($searchBody, 'filter', $clauseType);
        $aggregationQuery = $this->buildTermsAggregationQuery($aggregateField, $minDocCount, $entityName);
        $searchResult = $this->searchEntries($indexName, $entityName, $searchQuery, $aggregationQuery);
        $buckets = $searchResult['aggregations'][$entityName]['buckets'];
        $result = [];
        foreach ($buckets as $match) {
            $result[] = [
                $aggregateField => $match['key'],
                'doc_count' => $match['doc_count']
            ];
        }
        return $result;
    }

    /**
     * Searches entries into elastic search storage.
     *
     * @param string $indexName
     * @param string $entityName
     * @param array $searchQuery
     * @param array $aggregationQuery
     * @return array
     * @throws RuntimeException
     * @throws \OverflowException
     */
    private function searchEntries(
        string $indexName,
        string $entityName,
        array $searchQuery,
        array $aggregationQuery = []
    ): array {
        $searchBody['query'] = $searchQuery;
        $size = self::SEARCH_LIMIT;
        if (!empty($aggregationQuery)) {
            $searchBody['aggregations'] = $aggregationQuery;
            $size = 0;
        }
        return $this->searchRequest($indexName, $entityName, $searchBody, $size);
    }

    /**
     * Perform client search request.
     *
     * @param string $indexName
     * @param string $entityName
     * @param array $searchBody
     * @param int $size
     * @return array
     * @throws RuntimeException
     * @throws \OverflowException
     */
    private function searchRequest(string $indexName, string $entityName, array $searchBody, int $size): array
    {
        $query = [
            'index' => $indexName,
            'type' => $entityName,
            'body' => $searchBody,
            'size' => $size
        ];

        try {
            $result = $this->connectionPull->getConnection()->search($query);
        } catch (\Throwable $throwable) {
            throw new RuntimeException(
                __("Storage error: {$throwable->getMessage()} Query was:" . \json_encode($query)),
                $throwable
            );
        }

        //TODO: Add pagination and remove max size search size https://github.com/magento/catalog-storefront/issues/418
        if (isset($result['hits']['total']['value']) && $result['hits']['total']['value'] > self::SEARCH_LIMIT) {
            throw new \OverflowException(
                "Storage error: Search returned too many results to handle. Query was: " . \json_encode($query)
            );
        }

        return $result;
    }

    /**
     * Form a search query
     *
     * @param array $searchBody
     * @param string $queryContext
     * @param string $clauseType
     * @return array
     */
    private function buildSearchQuery(array $searchBody, string $queryContext, string $clauseType): array
    {
        $query = [];
        foreach ($searchBody as $key => $value) {
            $query['bool'][$queryContext][][$clauseType][$key] = $value;
        }
        return $query;
    }

    /**
     * Form a query for terms aggregation
     *
     * @param string $field
     * @param int $minDocCount
     * @param string $entityName
     * @return array
     */
    private function buildTermsAggregationQuery(string $field, int $minDocCount, string $entityName): array
    {
        //TODO: Add pagination and remove max size search size https://github.com/magento/catalog-storefront/issues/418
        $query[$entityName]['terms'] = [
            'size' => self::SEARCH_LIMIT,
            'field' => $field,
            'min_doc_count' => $minDocCount
        ];
        return $query;
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
    private function checkErrors(array $result, string $indexName): void
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
