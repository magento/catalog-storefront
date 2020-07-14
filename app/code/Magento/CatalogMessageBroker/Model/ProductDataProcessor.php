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
     * @var array
     */
    private $dataMappers;

    /**
     * @var string[]
     */
    private $fields;

    /**
     * @param array $dataMappers
     * @param string[] $fields
     */
    public function __construct(array $dataMappers, array $fields)
    {
        $this->dataMappers = $dataMappers;
        $this->fields = $fields;
    }

    /**
     * Override data returned from old API with data returned from new API
     * Checks for product type and whether fields are to be remapped
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
        $recursiveOverriddenFields = [];

        foreach ($this->fields as $field) {
            if (isset($data[$field])) {
                $overriddenFields[$field] = $data[$field];
            }
        }

        foreach ($this->dataMappers as $field => $dataMapperConfig) {
            if (
                array_key_exists('types', $dataMapperConfig) &&
                !in_array($product['type'], $dataMapperConfig['types'])
            ) {
                continue;
            }

            /** @var DataMapperInterface $dataMapper */
            $dataMapper = $dataMapperConfig['class'];

            if (array_key_exists('recursive', $dataMapperConfig) && $dataMapperConfig['recursive'] === true) {
                $recursiveOverriddenFields[$field] = $dataMapper->map($data);
            } else {
                $overriddenFields[$field] = $dataMapper->map($data);
            }
        }

        $product = array_merge($product, $overriddenFields);
        return array_replace_recursive($product, $recursiveOverriddenFields);
    }
}
