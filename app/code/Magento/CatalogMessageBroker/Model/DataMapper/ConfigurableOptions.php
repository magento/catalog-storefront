<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProductDataExporter\Model\Provider\Product\Options;

/**
 * Data mapper for configurable options
 */
class ConfigurableOptions implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(array $data): array
    {
        $configurableOptions = [];
        if ($data['type'] === Configurable::TYPE_CODE && !empty($data['options'])) {
            foreach ($data['options'] as $optionArray) {
                if ($optionArray['type'] === Options::CONFIGURABLE_RELATION_TYPE) {
                    $configurableOptions[$optionArray['id']] = [
                        'id' => $optionArray['id'],
                        'type' => $optionArray['type'],
                        'label' => $optionArray['title'],
                        'position' => $optionArray['sort_order'],
                        'product_id' => $data['product_id'],
                        'attribute_id' => $optionArray['attribute_id'],
                        'attribute_code' => $optionArray['attribute_code'],
                        'use_default' => $optionArray['use_default'],
                    ];

                    $configurableOptions[$optionArray['id']]['values'] = $this->mapOptionValues($optionArray);
                }
            }
        }
        return ['configurable_options' => $configurableOptions];
    }

    /**
     * Map option values
     *
     * @param array $optionArray
     * @return array
     */
    private function mapOptionValues(array $optionArray): array
    {
        $values = [];
        if (!empty($optionArray['values'])) {
            foreach ($optionArray['values'] as $value) {
                $values[$value['id']] = [
                    'value_index' => $value['id'],
                    'label' => $value['store_label'],
                    'default_label' => $value['default_label'],
                    'store_label' => $value['store_label'],
                    'use_default_value' => $value['store_label'] === $value['default_label']
                ];
            }
        }
        return $values;
    }
}