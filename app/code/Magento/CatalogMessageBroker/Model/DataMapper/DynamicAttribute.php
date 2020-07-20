<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Class DynamicAttribute for dynamic mapping
 */
class DynamicAttribute implements DataMapperInterface
{
    /**
     * Select option type
     */
    const SELECT_OPTION_TYPE = 'select';

    /**
     * Multiselect option type
     */
    const MULTISELECT_OPTION_TYPE = 'multiselect';

    /**
     * Boolean type
     */
    const BOOLEAN_TYPE = 'boolean';

    /**
     * Price type
     */
    const PRICE_TYPE = 'price';

    /**
     * Text type
     */
    const TEXT_TYPE = 'text';

    /**
     * @inheritDoc
     */
    public function map(array $productData): array
    {
        $attributes = [];
        foreach ($productData['attributes'] as $attribute) {
            if ($attribute['type'] == self::SELECT_OPTION_TYPE) {
                $attributes[$attribute['attribute_code']] = $attribute['value'][0]['id'];
            } else if ($attribute['type'] == self::MULTISELECT_OPTION_TYPE) {
                $values = [];
                foreach ($attribute['value'] as $attributeValue) {
                    $values[] = $attributeValue['id'];
                }
                $attributes[$attribute['attribute_code']] = implode(',', $values);
            } else if ($attribute['type'] == self::BOOLEAN_TYPE) {
                $attributes[$attribute['attribute_code']] = $attribute['value'][0]['value'] == 'yes' ? 1 : 0;
            } else if ($attribute['type'] == self::TEXT_TYPE || $attribute['type'] == self::PRICE_TYPE) {
                $attributes[$attribute['attribute_code']] = $attribute['value'][0]['value'];
            }
        }

        return $attributes;
    }
}
