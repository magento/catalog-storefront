<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogMessageBroker\Model\DataMapper\DataMapperInterface;

/**
 * Product data processor.
 */
class ProductDataProcessor
{
    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers;

    /**
     * @var string[]
     */
    private $fields;

    /**
     * @param DataMapperInterface[] $dataMappers
     * @param string[] $fields
     */
    public function __construct(array $dataMappers, array $fields)
    {
        $this->dataMappers = $dataMappers;
        $this->fields = $fields;
    }

    /**
     * Override data returned from old API with data returned from new API
     *
     * @param array $override
     * @param array $product
     * @return array
     */
    public function merge(array $override, array $product): array
    {
        $overriddenFields = [];

        foreach ($this->fields as $field) {
            if (isset($override[$field])) {
                $overriddenFields[$field] = $override[$field];
            }
        }

        /** @var DataMapperInterface $dataMapper */
        foreach ($this->dataMappers as $field => $dataMapper) {
            $overriddenFields[$field] = $dataMapper->map($override);
        }

        return array_merge($product, $overriddenFields);
    }
}
