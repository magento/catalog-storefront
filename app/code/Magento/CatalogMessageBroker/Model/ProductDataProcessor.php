<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogMessageBroker\Model\DataMapper\DataMapperInterface;

/**
 * Product data processor that merges data from old and new providers.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     *
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
        $scalarFields = $this->mergeScalarFields($data);
        $compoundFields = $this->mergeCompoundFields($data, $product);
        return array_merge($product, $scalarFields, $compoundFields);
    }

    /**
     * Merge scalar fields in product data
     *
     * @param array $data
     * @return array
     */
    private function mergeScalarFields($data): array
    {
        $fieldsData = [];
        foreach ($this->fields as $field) {
            if (isset($data[$field])) {
                $fieldsData[$field] = $data[$field];
            }
        }
        return $fieldsData;
    }

    /**
     * Merge compound fields in product data using data mappers
     *
     * @param array $data
     * @param array $product
     * @return array
     */
    private function mergeCompoundFields($data, $product): array
    {
        $fields = [];
        foreach ($this->dataMappers as $field => $dataMapperConfig) {
            if (array_key_exists('types', $dataMapperConfig) &&
                !in_array($product['type_id'], $dataMapperConfig['types'])
            ) {
                continue;
            }

            /** @var DataMapperInterface $dataMapper */
            $dataMapper = $dataMapperConfig['class'];

            if (array_key_exists('recursive', $dataMapperConfig) && $dataMapperConfig['recursive'] === true) {
                $fields[$field] = array_replace_recursive($product[$field], $dataMapper->map($data)[$field]);
            } else {
                $fields[$field] = $dataMapper->map($data)[$field];
            }
        }
        return array_filter($fields);
    }
}
