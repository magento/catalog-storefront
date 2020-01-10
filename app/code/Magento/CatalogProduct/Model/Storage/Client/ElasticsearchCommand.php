<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Client;

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
    public function bulkInsert(string $dataSourceName, string $entityName, array $entries)
    {
        $query = $this->getDocsArrayInBulkIndexFormat($dataSourceName, $entityName, $entries);
        try {
            $this->getConnection()->bulk($query);
        } catch (\Throwable $throwable) {
            throw new BulkException(
                __("Error occurred while bulk insert to '$dataSourceName' index."),
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
            // 'type' => $entityName,
            'body' => [],
            'refresh' => true,
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
            }
        }

        return $bulkArray;
    }
}
