<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Data mapper for custom options
 */
class CustomOptions implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(array $override): array
    {
        $productCustomOptions = [];

        if (!empty($override['options'])) {
            $productSelectableOptions = $override['options'];
            $customOptions = array_filter(
                $productSelectableOptions,
                function ($value) {
                    return $value['type'] == 'custom_option';
                }
            );
            foreach ($customOptions as $customOption) {
                $customOptionValues = [];
                foreach ($customOption['values'] as $value) {
                    $customOptionValue = $value;
                    $customOptionValue['price'] = current($value['price']);
                    $customOptionValue['title'] = $value['value'];
                    $customOptionValue['option_type_id'] = $value['id'];
                    unset($value['value']);
                    $customOptionValues[$value['id']] = $customOptionValue;
                }
                unset($customOption['values']);
                $customOption['value'] = $customOptionValues;
                $customOption['type'] = $customOption['render_type'];
                $customOption['option_id'] = $customOption['id'];
                $productCustomOptions[] = $customOption;
            }
        }

        if (!empty($override['entered_options'])) {
            $productEnteredOptions = $override['entered_options'];
            foreach ($productEnteredOptions as $customOption) {
                $customOption['price'] = current($customOption['price']);
                $customOption['title'] = $customOption['value'];
                $customOption['type'] = $customOption['render_type'];
                $customOption['option_id'] = $customOption['id'];
                $customOption['value'] = $customOption;
                $productCustomOptions[] = $customOption;
            }
        }

        return $productCustomOptions;
    }
}
