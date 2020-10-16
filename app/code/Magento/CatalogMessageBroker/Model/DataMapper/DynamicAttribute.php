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
        $dynamicAttributes = [];

        foreach ($productData['attributes'] ?? [] as $attribute) {
            $values = [];
            foreach ($attribute['value'] ?? [] as $option) {
                $values[] = $option['value'];
            }
            $dynamicAttributes[] = [
                'code' => $attribute['attribute_code'],
                'type' => $attribute['type'],
                'values' => $values
            ];
        }

        return $dynamicAttributes ? ['attributes' => $dynamicAttributes] : [];
    }
}
