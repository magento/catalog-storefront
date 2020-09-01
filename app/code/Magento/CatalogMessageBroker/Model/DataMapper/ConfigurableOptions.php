<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Data mapper for configurable options
 */
class ConfigurableOptions implements DataMapperInterface
{
    /**
     * Configurable Product Super Option Type comes from Export API
     */
    private const CONFIGURABLE_RELATION_TYPE = 'super';

    /**
     * Configurable Product type code.
     */
    private const CONFIGURABLE_TYPE_CODE = 'configurable';

    /**
     * @inheritDoc
     */
    public function map(array $data): array
    {
        if ($data['type'] === self::CONFIGURABLE_TYPE_CODE && !empty($data['options'])) {
            $configurableOptions = [];

            foreach ($data['options'] as $optionArray) {
                if ($optionArray['type'] === self::CONFIGURABLE_RELATION_TYPE) {
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

            return ['configurable_options' => $configurableOptions];
        }

        return [];
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
