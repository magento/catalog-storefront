<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogMessageBroker\Model\DataMapper\DataMapperInterface;

/**
 * Processing data for the product.
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
     * @param array $data
     * @param array $product
     * @return array
     * @deprecated this is a temporary solution that will be replaced
     * with declarative schema of mapping exported data format to storefront format
     */
    public function merge(array $data, array $product): array
    {
        $overriddenFields = [];

        foreach ($this->fields as $field) {
            if (isset($data[$field])) {
                $overriddenFields[$field] = $data[$field];
            }
        }

        /** @var DataMapperInterface $dataMapper */
        foreach ($this->dataMappers as $field => $dataMapper) {
            $overriddenFields[$field] = $dataMapper->map($data);
        }

        return array_merge($product, $overriddenFields);
    }
}
