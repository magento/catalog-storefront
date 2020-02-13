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
        $this->data['id'] = (int)$data['_id'];
        $this->data = $data['_source'];
    }

    /**
     * @inheritdoc
     */
    public function getId(): int
    {
        return $this->data['id'];
    }

    /**
     * @inheritdoc
     */
    public function getData(string $field = '')
    {
        if ('' !== $field) {
            return isset($this->data[$field]) ? $this->data[$field] : null;
        }
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function getVariants(): EntryIteratorInterface
    {
        return $this->data['variants'] ?? [];
    }
}
