<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\Converter;

/**
 * Class responsible for converting attribute codes from camel case to snake case
 */
class AttributeCodesConverter
{
    /**
     * @var array
     */
    private $convertedAttributesCache;

    /**
     * Convert attribute codes from camel case to snake case
     *
     * @param array $attributeCodes
     *
     * @return array
     */
    public function convertFromCamelCaseToSnakeCase(array $attributeCodes): array
    {
        $attributes = [];

        foreach ($attributeCodes as $attributeCode) {
            if (!isset($this->convertedAttributesCache[$attributeCode])) {
                $this->convertedAttributesCache[$attributeCode] = $this->camelCaseToSnakeCase($attributeCode);
            }

            $attributes[] = $this->convertedAttributesCache[$attributeCode];
        }

        return $attributes;
    }

    /**
     * Convert a CamelCase string into snake_case
     *
     * @param string $string
     *
     * @return string
     */
    private function camelCaseToSnakeCase(string $string): string
    {
        return \strtolower(\preg_replace('/(.)([A-Z])/', '$1_$2', $string));
    }
}
