<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\DataProvider;

/**
 * Transform attributes from raw value to formatted output
 *
 * Input:
 *  [
 *      'product_id' => [
 *          'input_attribute1',
 *          'input_attribute2',
 *      ]
 *  ];
 *
 * Output:
 *  [
 *      'product_id' => [
 *          'input_attribute1' => 'output_attribute',
 *          'input_attribute2' => [
 *              'output_attribute1',
 *              'output_attribute2',
 *          ],
 *      ]
 *  ];
 */
interface TransformerInterface
{
    /**
     * Transform data
     *
     * @param array $productItems
     * @param array $attributes
     * @return array
     */
    public function transform(array $productItems, array $attributes): array;
}
