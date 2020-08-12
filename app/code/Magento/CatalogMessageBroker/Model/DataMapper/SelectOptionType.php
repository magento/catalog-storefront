<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Class for select type
 */
class SelectOptionType implements AttributeTypeInterface
{
    /**
     * Get attribute
     *
     * @param array $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return $attribute['value'][0]['id'];
    }
}
