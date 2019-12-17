<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Data;

/**
 * Storage Entry Iterator implementation for elasticsearch documents.
 */
class DocumentIterator implements EntryIteratorInterface
{
    /**
     * @var array
     */
    private $documents;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @param Document[] $documents
     */
    public function __construct(array $documents)
    {
        $this->documents = $documents;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return isset($this->documents[$this->position]);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @inheritdoc
     */
    public function current(): EntryInterface
    {
        return $this->documents[$this->position];
    }
}
