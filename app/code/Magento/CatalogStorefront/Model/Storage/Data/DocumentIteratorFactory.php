<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Data;

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
        $documents = [];
        foreach ($data['docs'] as $item) {
            $documents[] = $this->documentFactory->create($item);
        }

        return $this->objectManager->create(DocumentIterator::class, ['documents' => $documents]);
    }
}
