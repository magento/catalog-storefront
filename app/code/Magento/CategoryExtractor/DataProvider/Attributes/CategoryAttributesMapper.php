<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider\Attributes;

use \Magento\CatalogStorefrontConnector\DataProvider\Query\AttributesDataConverter;

/**
 * Map for category attributes.
 */
class CategoryAttributesMapper
{
    /**
     * @var array
     */
    private $arrayTypeAttributes = [
        'available_sort_by',
    ];

    /**
     * @var AttributesDataConverter
     */
    private $attributesDataConverter;

    /**
     * @param AttributesDataConverter $attributesDataConverter
     */
    public function __construct(AttributesDataConverter $attributesDataConverter)
    {
        $this->attributesDataConverter = $attributesDataConverter;
    }

    /**
     * Returns attribute values for given attribute codes.
     *
     * @param array $fetchResult
     * @return array
     */
    public function getAttributesValues(array $fetchResult): array
    {
        $attributes = $this->attributesDataConverter->convert($fetchResult);

        return $this->formatAttributes($attributes);
    }

    /**
     * Format attributes that should be converted to array type
     *
     * @param array $attributes
     * @return array
     */
    private function formatAttributes(array $attributes): array
    {
        return $this->arrayTypeAttributes
            ? array_map(
                function ($data) {
                    foreach ($this->arrayTypeAttributes as $attributeCode) {
                        $data[$attributeCode] = $this->valueToArray($data[$attributeCode] ?? null);
                    }
                    return $data;
                },
                $attributes
            )
            : $attributes;
    }

    /**
     * Cast string to array
     *
     * @param string|null $value
     * @return array
     */
    private function valueToArray($value): array
    {
        return $value ? \explode(',', $value) : [];
    }
}
