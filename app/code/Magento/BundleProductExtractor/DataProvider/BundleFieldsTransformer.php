<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleProductExtractor\DataProvider;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\CatalogExtractor\DataProvider\TransformerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Transform bundle product attributes.
 */
class BundleFieldsTransformer implements TransformerInterface
{
    /**
     * Map of table fields to schema fields.
     *
     * Example:
     * ```
     * 'sku_type' => 'dynamic_sku'
     * ```
     * sku_type - eav attribute in db
     * dynamic_sku - name of requested attribute
     *
     * @var array
     */
    private $attributeFieldsMap = [
        'weight_type' => 'dynamic_weight',
        'price_type' => 'dynamic_price',
        'sku_type' => 'dynamic_sku',
    ];

    /**
     *
     * Map of table fields to schema fields.
     *
     * Example:
     * ```
     * 'shipment_type' => [
     *   'enumName' => 'ShipBundleItemsEnum',
     *   'attribute' => 'ship_bundle_items',
     * ]
     * ```
     * shipment_type - eav attribute in db
     * ship_bundle_items - name of requested attribute
     *
     * @var array
     */
    private $attributeEnumFieldsMap = [
        'shipment_type' => [
            'enumName' => 'ShipBundleItemsEnum',
            'attribute' => 'ship_bundle_items',
        ]
    ];

    /**
     * Enum values list.
     *
     * @var array
     */
    private $enumValues = [
        'ShipBundleItemsEnum' => [
            'TOGETHER',
            'SEPARATELY',
        ]
    ];

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function transform(array $productItems, array $attributes): array
    {
        foreach ($productItems as &$item) {
            if ($item['type_id'] !== BundleType::TYPE_CODE) {
                continue;
            }
            foreach ($attributes as $attributeName => $outputAttribute) {
                $rawValue = $item[$attributeName] ?? '';
                $item[$outputAttribute] = [];

                if (isset($this->attributeFieldsMap[$outputAttribute])) {
                    $item[$this->attributeFieldsMap[$outputAttribute]]
                        = isset($item[$outputAttribute]) ? !$rawValue : null;
                    continue;
                }

                if (isset($this->attributeEnumFieldsMap[$outputAttribute])) {
                    $enumName = $this->attributeEnumFieldsMap[$outputAttribute]['enumName'];

                    $rawValue = (int)$rawValue;
                    if (empty($this->enumValues[$enumName][$rawValue])) {
                        throw new LocalizedException(
                            __('Enum list "%1" does not have value for "%2"', $enumName, $rawValue)
                        );
                    }
                    $requestedAttribute = $this->attributeEnumFieldsMap[$outputAttribute]['attribute'];
                    $item[$requestedAttribute] = $this->enumValues[$enumName][$rawValue];
                    continue;
                }
            }
        }

        return $productItems;
    }
}
