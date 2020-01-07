<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Data;

/**
 * Document iterator factory.
 */
class DocumentIteratorFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * @var DocumentIteratorFactory
     */
    private $documentFactory;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param DocumentFactory $documentFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        DocumentFactory $documentFactory
    ) {
        $this->objectManager = $objectManager;
        $this->documentFactory = $documentFactory;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return DocumentIterator
     */
    public function create(array $data = []): DocumentIterator
    {
        $subDocuments = [];
        $nestedEntries = isset($data['aggregations']['nested_entries'])
            ? $data['aggregations']['nested_entries']
            : [];

        if (!empty($nestedEntries) && $nestedEntries['doc_count'] > 0) {
            foreach ($nestedEntries['variants']['hits']['hits'] as $item) {
                $subDocuments[(int)$item['_routing']][] = $this->documentFactory->create($item);
            }
        }

        $items = isset($data['hits']['hits'])
            ? $data['hits']['hits']
            : $data['docs'];

        $documents = [];
        foreach ($items as $item) {
            if (!empty($subDocuments) && isset($subDocuments[$item['_id']])) {
                $item['variants'] = $this->objectManager->create(
                    DocumentIterator::class,
                    ['documents' => $subDocuments[$item['_id']]]
                );
            }
            $documents[] = $this->documentFactory->create($item);
        }

        return $this->objectManager->create(DocumentIterator::class, ['documents' => $documents]);
    }
}
