<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\Transformer;

use Magento\CatalogProduct\DataProvider\TransformerInterface;
use Magento\Catalog\Helper\Output as OutputHelper;

/**
 * Transform product description attribute
 *
 * [description] >>> [description => [html]]
 */
class DescriptionTransformer implements TransformerInterface
{
    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @param OutputHelper $outputHelper
     */
    public function __construct(OutputHelper $outputHelper)
    {
        $this->outputHelper = $outputHelper;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function transform(array $productItems, array $attributes): array
    {
        foreach ($productItems as &$item) {
            foreach (array_keys($attributes) as $attributeName) {
                $rawValue = $item[$attributeName] ?? '';
                $item[$attributeName] = [];

                $item[$attributeName]['html'] = !empty($rawValue)
                    ? $this->outputHelper->productAttribute(null, $rawValue, $attributeName)
                    : '';
            }
        }

        return $productItems;
    }
}
