<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Data;

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
        $result = $this->data['_source'];

        if ('' !== $field) {
            $result = $result[$field];
        }

        return $result;
    }
}
