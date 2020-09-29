<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\Converter;

use Magento\Framework\Api\SimpleDataObjectConverter;

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
                $this->convertedAttributesCache[$attributeCode] =
                    SimpleDataObjectConverter::camelCaseToSnakeCase($attributeCode);
            }

            $attributes[] = $this->convertedAttributesCache[$attributeCode];
        }

        return $attributes;
    }
}
