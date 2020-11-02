<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Data;

use Magento\Framework\ObjectManagerInterface;

/**
 * Search result iterator factory.
 */
class SearchResultIteratorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DocumentIteratorFactory
     */
    private $documentFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DocumentFactory $documentFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DocumentFactory $documentFactory
    ) {
        $this->objectManager = $objectManager;
        $this->documentFactory = $documentFactory;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $result
     *
     * @return DocumentIterator
     */
    public function create(array $result): DocumentIterator
    {
        $documents = [];

        foreach ($result['hits']['hits'] as $item) {
            $documents[(int)$item['_id']] = $this->documentFactory->create($item);
        }

        return $this->objectManager->create(DocumentIterator::class, ['documents' => $documents]);
    }
}
