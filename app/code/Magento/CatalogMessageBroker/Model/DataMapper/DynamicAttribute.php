<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Class DynamicAttribute for dynamic attributes
 */
class DynamicAttribute implements DataMapperInterface
{
    const SELECT = 'select';
    const MULTISELECT = 'multiselect';

    /**
     * @inheritDoc
     */
    public function map(array $productData): array
    {
        $attributes = [];
        foreach ($productData['attributes'] as $attribute) {
            if ($attribute['type'] == self::SELECT) {
                $attributes[$attribute['attribute_code']] = $attribute['value'][0]['id'];
            } else if ($attribute['type'] == self::MULTISELECT) {
                $values = [];
                foreach ($attribute['value'] as $attributeValue) {
                    $values[] = $attributeValue['id'];
                }
                $attributes[$attribute['attribute_code']] = implode(',', $values);
            } else if ($attribute['type'] == 'boolean') {
                $attributes[$attribute['attribute_code']] = $attribute['value'][0]['value'] == 'yes' ? 1 : 0;
            } else if ($attribute['type'] == 'text' || $attribute['type'] == 'price') {
                $attributes[$attribute['attribute_code']] = $attribute['value'][0]['value'];
            }
        }

        return $attributes;
    }
}
