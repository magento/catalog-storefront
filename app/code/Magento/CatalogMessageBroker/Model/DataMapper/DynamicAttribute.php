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
     * @inheritDoc
     */
    public function map(array $productData): array
    {
        $dynamicAttributes = [];

        foreach ($productData['attributes'] ?? [] as $attribute) {
            $dynamicAttributes[] = [
                'code' => $attribute['attribute_code'],
                'type' => $attribute['type'],
                'values' => $attribute['value']
            ];
        }

        return $dynamicAttributes ? ['attributes' => $dynamicAttributes] : [];
    }
}
