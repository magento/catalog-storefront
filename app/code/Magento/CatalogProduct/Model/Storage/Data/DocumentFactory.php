<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Data;

/**
 * Document factory.
 */
class DocumentFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Document
     */
    public function create(array $data = []): Document
    {
        if (isset($data['hits']['hits'])) {
            $nestedEntries = isset($data['aggregations']['nested_entries'])
                ? $data['aggregations']['nested_entries']
                : [];
            $data = $data['hits']['hits'][0];
            $subDocuments = [];

            if (!empty($nestedEntries) && $nestedEntries['doc_count'] > 0) {
                foreach ($nestedEntries['variants']['hits']['hits'] as $item) {
                    $subDocuments[] = $this->create($item);
                }
                $data['variants'] = $this->objectManager->create(
                    DocumentIterator::class,
                    ['documents' => $subDocuments]
                );
            }
        }

        return $this->objectManager->create(Document::class, ['data' => $data]);
    }
}
