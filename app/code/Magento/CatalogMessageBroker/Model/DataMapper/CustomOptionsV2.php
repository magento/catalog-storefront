<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Data mapper for custom options V2
 */
class CustomOptionsV2 implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(array $data): array
    {
        $productSelectableOptions = [];

        if (!empty($data['options_v2'])) {
            $productSelectableOptionsV2 = $data['options_v2'];
            $customOptions = \array_filter(
                $productSelectableOptionsV2,
                function ($value) {
                    return $value['type'] == 'custom_option';
                }
            );
            foreach ($customOptions as $customOption) {
                $customOptionValues = [];

                foreach ($customOption['values'] as $value) {
                    $customOptionValue = [
                        'id' => $value['id'],
                        'label' => $value['label'],
                        'sort_order' => $value['sort_order'],
                        'default' => $value['default'],
                    ];
                    $customOptionValues[] = $customOptionValue;
                }
                unset($customOption['values']);

                $selectableOption = [
                    'values' => $customOptionValues,
                    'id' => $customOption['id'],
                    'label' => $customOption['label'],
                    'sort_order' => $customOption['sort_order'],
                    'required' => $customOption['required'],
                    'render_type' => $customOption['render_type'],
                    'type' => $customOption['type'],
                ];
                $productSelectableOptions[] = $selectableOption;
            }
        }

        return ['options_v2' => $productSelectableOptions];
    }
}
