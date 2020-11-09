<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Gift card option values sorting by price
 */
class GiftCardProductOptions implements DataMapperInterface
{
    /**
     * Gift card product type
     */
    private const PRODUCT_TYPE_GIFTCARD = 'giftcard';

    /**
     * @inheritDoc
     */
    public function map(array $productData): array
    {
        if (!isset($productData['type']) ||
            $productData['type'] !== self::PRODUCT_TYPE_GIFTCARD ||
            empty($productData['product_options'])
        ) {
            return [];
        }

        foreach ($productData['product_options'] as &$option) {
            if ($option['type'] !== self::PRODUCT_TYPE_GIFTCARD || empty($option['values'])) {
                continue;
            }

            \usort($option['values'], function ($a, $b) {
                return $a['price'] > $b['price'];
            });
        }

        return ['product_options' => $productData['product_options']];
    }
}
