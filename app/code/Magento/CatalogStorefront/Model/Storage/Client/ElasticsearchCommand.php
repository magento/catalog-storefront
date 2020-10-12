<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client;

use Magento\Framework\Exception\BulkException;

/**
 * Elasticsearch client for write access operations.
 */
class ElasticsearchCommand implements CommandInterface
{
    /**#@+
     * Text flags for Elasticsearch bulk actions.
     */
    private const BULK_ACTION_INDEX = 'index';
    private const BULK_ACTION_CREATE = 'create';
    private const BULK_ACTION_DELETE = 'delete';
    private const BULK_ACTION_UPDATE = 'update';
    /**#@-*/

    /**#@+
     * Text flags for Elasticsearch error types
     */
    private const ERROR_TYPE_INDEX_NOT_FOUND = 'index_not_found_exception';
    /**#@-*/

    /**
     * @var ConnectionPull
     */
    private $connectionPull;

    /**
     * Initialize Elasticsearch Client
     *
     * @param ConnectionPull $connectionPull
     */
    public function __construct(
        ConnectionPull $connectionPull
    ) {
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
    public function bulkInsert(string $dataSourceName, string $entityName, array $entries): void
    {
        $query = $this->getDocsArrayInBulkIndexFormat($dataSourceName, $entityName, $entries, self::BULK_ACTION_INDEX);
        try {
            $result = $this->getConnection()->bulk($query);
            $error = $result['errors'] ?? false;
            if ($error) {
                $this->handleBulkError($result['items'] ?? [], self::BULK_ACTION_INDEX);
            }
        } catch (\Throwable $throwable) {
            throw new BulkException(
                __(
                    'Error occurred while bulk insert to "%1" index. Entity ids: "%2". Error: %3',
                    $dataSourceName,
                    \array_column($entries, 'id'),
                    $throwable->getMessage()
                ),
                $throwable
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function bulkUpdate(string $dataSourceName, string $entityName, array $entries): void
    {
        $query = $this->getDocsArrayInBulkIndexFormat($dataSourceName, $entityName, $entries, self::BULK_ACTION_UPDATE);

        try {
            $result = $this->getConnection()->bulk($query);
            $error = $result['errors'] ?? false;
            if ($error) {
                $this->handleBulkError($result['items'] ?? [], self::BULK_ACTION_UPDATE);
            }
        } catch (\Throwable $throwable) {
            throw new BulkException(
                __(
                    'Error occurred while bulk update to "%1" index. Entity ids: "%2". Error: %3',
                    $dataSourceName,
                    \array_column($entries, 'id'),
                    $throwable->getMessage()
                ),
                $throwable
            );
        }
    }

    /**
     * Reformat documents array to bulk format.
     *
     * @param string $indexName
     * @param string $entityName
     * @param array $documents
     * @param string $action
     * @return array
     */
    private function getDocsArrayInBulkIndexFormat(
        string $indexName,
        string $entityName,
        array $documents,
        string $action = self::BULK_ACTION_INDEX
    ): array {
        $bulkArray = [
            'index' => $indexName,
            'type' => $entityName,
            'body' => [],
            'refresh' => false,
        ];

        foreach ($documents as $document) {
            $metaInfo = [
                '_id' => $document['id'],
                '_type' => $entityName,
                '_index' => $indexName
            ];
            if (isset($document['parent_id']['parent'])) {
                $metaInfo['routing'] = $document['parent_id']['parent'];
            }
            $bulkArray['body'][] = [
                $action => $metaInfo
            ];

            if ($action === self::BULK_ACTION_INDEX) {
                $bulkArray['body'][] = $document;
            } elseif ($action === self::BULK_ACTION_UPDATE) {
                $bulkArray['body'][]['doc'] = $document;
            }
        }

        return $bulkArray;
    }

    /**
     * Handle error on Bulk insert
     *
     * @param array $items
     * @param string $action
     * @return void
     */
    private function handleBulkError(array $items, string $action): void
    {
        $errors = [];
        foreach ($items as $item) {
            $error = $item[$action]['error'] ?? null;
            if ($error && ($item[$action]['error']['type'] ?? '') !== self::ERROR_TYPE_INDEX_NOT_FOUND) {
                $item = $item[$action];
                $errors[] = \sprintf(
                    'id: %s, status: %s, error: %s',
                    $item['_id'],
                    $item['status'],
                    ($error['type'] ?? '') . ': ' . ($error['reason'] ?? '')
                );
            }
        }
        if ($errors) {
            throw new \LogicException('List of errors: ' . \json_encode($errors));
        }
    }

    /**
     * @inheritdoc
     *
     * @throws BulkException
     */
    public function bulkDelete(string $dataSourceName, string $entityName, array $ids): void
    {
        $documents = \array_map(
            function ($id) {
                return ['id' => $id];
            },
            $ids
        );
        $query = $this->getDocsArrayInBulkIndexFormat(
            $dataSourceName,
            $entityName,
            $documents,
            self::BULK_ACTION_DELETE
        );
        try {
            $result = $this->getConnection()->bulk($query);
            $error = $result['errors'] ?? false;
            if ($error) {
                $this->handleBulkError($result['items'] ?? [], self::BULK_ACTION_DELETE);
            }
        } catch (\Throwable $throwable) {
            throw new BulkException(
                __(
                    'Error occurred while bulk delete from "%1" index. Entity ids: "%2"',
                    $dataSourceName,
                    \implode(',', $ids)
                ),
                $throwable
            );
        }
    }
}
