<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Class for multiselect
 */
class MultiselectOptionType implements AttributeTypeInterface
{
    /**
     * @param $attribute
     * @return string
     */
    public function getAttribute($attribute)
    {
        $values = [];
        foreach ($attribute['value'] as $attributeValue) {
            $values[] = $attributeValue['id'];
        }

        return implode(',', $values);
    }
}
