<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Data mapper for custom options
 */
class CustomOptions implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(array $data): array
    {
        $productCustomOptions = [];

        if (!empty($data['options'])) {
            $productSelectableOptions = $data['options'];
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
                    $customOptionValue['price'] = $value['price']['final_price'];
                    $customOptionValue['title'] = $value['value'];
                    $customOptionValue['option_type_id'] = $value['id'];
                    $customOptionValue['price_type'] = strtoupper($value['price_type']);
                    $customOption['required'] = $customOption['is_required'];
                    unset($value['value']);
                    $customOptionValues[] = $customOptionValue;
                }
                unset($customOption['values']);
                $customOption['value'] = $customOptionValues;
                $customOption['type'] = $customOption['render_type'];
                $customOption['option_id'] = $customOption['id'];
                $customOption['required'] = $customOption['is_required'];
                $productCustomOptions[] = $customOption;
            }
        }

        if (!empty($data['product_shopper_input_options'])) {
            $productShopperInputOptions = $data['product_shopper_input_options'];
            foreach ($productShopperInputOptions as $customOption) {
                $customOption['option_id'] = $customOption['id'];
                $customOption['type'] = $customOption['render_type'];
                $customOption['value'] = [$customOption];
                $productCustomOptions[] = $customOption;
            }
        }

        return ['options' => $productCustomOptions];
    }
}
