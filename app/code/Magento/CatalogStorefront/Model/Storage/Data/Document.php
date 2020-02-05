<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Data;

/**
 * Storage Entry implementation for elasticsearch document.
 */
class Document implements EntryInterface
{
    /**
     * @var array $data
     */
    private $data;

    /**
     * Document constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $data['_id'] = (int)$data['_id'];
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function getId(): int
    {
        return $this->data['_id'];
    }

    /**
     * @inheritdoc
     */
    public function getData(string $field = '')
    {
        // handle get/mget query when document was not found in index
        if (isset($this->data['found']) && $this->data['found'] === false) {
            return null;
        }

        $result = $this->data['_source'];

        if ('' !== $field) {
            $result = $result[$field] ?? null;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getVariants(): EntryIteratorInterface
    {
        return $this->data['variants'] ?? [];
    }
}
