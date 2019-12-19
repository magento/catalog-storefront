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
    public function create(array $data = []) : Document
    {
        if (isset($data['data']['aggregations'])) {
            $result = $data['data']['hits']['hits'][0];
            if ($data['data']['aggregations']['nested_products']['doc_count'] > 0) {
                $documents = [];
                foreach ($data['data']['aggregations']['nested_products']['variants']['hits']['hits'] as $item) {
                    $documents[] = $this->create(['data' => $item]);
                }
                $result['variants'] = $this->objectManager->create(
                    DocumentIteratorFactory::class,
                    ['documents' => $documents]
                );
            }

            return $this->objectManager->create(Document::class, ['data' => $result]);
        } else {
            return $this->objectManager->create(Document::class, $data);
        }
    }
}
