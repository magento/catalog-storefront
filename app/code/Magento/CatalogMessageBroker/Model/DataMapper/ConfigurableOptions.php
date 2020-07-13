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
     * @inheritDoc
     */
    public function map(array $data): array
    {
        $configurableOptions = [];
        if ($data['type'] === 'configurable' && !empty($data['options'])) {
            foreach ($data['options'] as $optionArray) {
                if ($optionArray['type'] === 'super') {
                    $configurableOptions[$optionArray['id']] = [ //add array key of attribute id
                        'id' => $optionArray['id'],
                        'type' => $optionArray['type'],
                        'label' => $optionArray['label'],
                        'position' => $optionArray['sort_order'], //remap sort_order to position
                        'product_id' => $data['id']
                    ];

                    if (!empty($optionArray['values'])) {
                        foreach ($optionArray['values'] as $value) {
                            $configurableOptions[$optionArray['id']]['values'][$value['id']] = [ //add array key of attribute value label id
                                'value_index' => $value['id'], //remap id to value_index
                                'label' => $value['label']
                            ];
                        }
                    }
                }
            }
        }
        return $configurableOptions;
    }
}
