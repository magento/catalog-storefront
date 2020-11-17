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
        $this->data = $data['_source'] ?? [];
        $this->data['id'] = $this->data['id'] ?? (string)$data['_id'];
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->data['id'];
    }

    /**
     * @inheritdoc
     */
    public function getData(string $field = '')
    {
        if ('' !== $field) {
            return $this->data[$field] ?? null;
        }
        return $this->data;
    }
}
