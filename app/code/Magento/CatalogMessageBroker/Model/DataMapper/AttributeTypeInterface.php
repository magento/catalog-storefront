<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Interface for getting attributes
 */
interface AttributeTypeInterface
{
    /**
     * Get attribute data
     *
     * @param array $attribute
     * @return mixed
     */
    public function getAttribute($attribute);
}
