<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Class for the boolean type
 */
class BooleanType implements AttributeTypeInterface
{
    /**
     * @param $attribute
     * @return int
     */
    public function getAttribute($attribute)
    {
        return $attribute['value'][0]['value'] == 'yes' ? 1 : 0;
    }
}

