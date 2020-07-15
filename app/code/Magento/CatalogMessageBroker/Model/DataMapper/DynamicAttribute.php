<?php


namespace Magento\CatalogMessageBroker\Model\DataMapper;


class DynamicAttribute implements DataMapperInterface
{
    public function map(array $productData): array
    {
        $attributes = [];
        foreach ($productData['attributes'] as $attribute) {
            if ($attribute['type'] == 'select') {
                $attributes[$attribute['attribute_code']] = $attribute['value'][0]['id'];
            } else if ($attribute['type'] == 'multiselect') {
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
