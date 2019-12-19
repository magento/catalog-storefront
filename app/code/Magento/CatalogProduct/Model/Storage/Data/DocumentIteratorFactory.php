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
    public function create(array $data = []) : DocumentIterator
    {
        if (isset($data['documents']['hits'])) {
            $documents = [];
            foreach ($result['docs'] as $item) {
                $documents[] = $this->documentFactory->create(['data' => $item]);
            }
            return $this->documentIteratorFactory->create(['documents' => $documents]);

            $result = $data['data']['hits']['hits'][0];
            if ($data['data']['aggregations']['nested_products']['doc_count'] > 0) {
                $documents = [];
                foreach ($data['data']['aggregations']['nested_products']['variants']['hits']['hits'] as $item) {
                    $documents[] = $this->create(['data' => $item]);
                }
                $result['variants'] = $this->documentFactory->create(['documents' => $documents]);
            }

            return $this->objectManager->create(DocumentIterator::class, ['data' => $result]);
        } else {
            return $this->objectManager->create(DocumentIterator::class, $data);
        }
    }
}
