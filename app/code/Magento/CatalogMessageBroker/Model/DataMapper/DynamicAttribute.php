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
     * @var array
     */
    private $attributePool;

    /**
     * @param array $attributePool
     */
    public function __construct(array $attributePool)
    {
        $this->attributePool = $attributePool;
    }

    /**
     * @inheritDoc
     */
    public function map(array $productData): array
    {
        $attributes = [];
        if ( $productData && $productData['attributes']) {
            foreach ($productData['attributes'] as $attribute) {
                $attributes[$attribute['attribute_code']] = $this->attributePool[$attribute['type']]->getAttribute($attribute);
            }
        }

        return $attributes;
    }
}
