<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Data mapper for bundle items
 */
class BundleItems implements DataMapperInterface
{
    /**
     * Product entity bundle product type
     */
    private const PRODUCT_TYPE_BUNDLE = 'bundle';

    /**
     * @inheritDoc
     */
    public function map(array $data) : array
    {
        if ($data['type'] !== self::PRODUCT_TYPE_BUNDLE || empty($data['options'])) {
            return [];
        }

        return [
            'items' => \array_map(function ($option) {
                $data = $this->formatBundleItemData($option);

                if (!empty($option['values'])) {
                    $data['options'] = \array_map(function ($value) {
                        return $this->formatBundleItemOptionsData($value);
                    }, $option['values']);
                }

                return $data;
            }, $data['options'])
        ];
    }

    /**
     * Format bundle item data
     *
     * @param array $data
     *
     * @return array
     */
    private function formatBundleItemData(array $data) : array
    {
        return [
            'option_id' => $data['id'],
            'title' => $data['title'],
            'required' => $data['is_required'],
            'type' => $data['render_type'],
            'position' => $data['sort_order'],
            'sku' => $data['product_sku'],
        ];
    }

    /**
     * Format bundle item options data
     *
     * @param array $data
     *
     * @return array
     */
    private function formatBundleItemOptionsData(array $data) : array
    {
        return [
            'id' => $data['id'],
            'label' => $data['label'],
            'quantity' => $data['quantity'],
            'position' => $data['sort_order'],
            'is_default' => $data['is_default'],
            'price' => $data['price']['final_price'],
            'price_type' => $data['price_type'],
            'can_change_quantity' => $data['can_change_quantity'],
            'entity_id' => $data['entity_id'],
        ];
    }
}
