<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\MediaGallery;

use Magento\CatalogProduct\DataProvider\TransformerInterface;

/**
 * Transform product image attribute
 *
 * [image_attribute] >>> [image_attribute => [url, label]]
 */
class ProductImageTransformer implements TransformerInterface
{
    /**
     * @var ImageUrlResolver
     */
    private $imageUrlResolver;

    /**
     * @param ImageUrlResolver $imageUrlResolver
     */
    public function __construct(ImageUrlResolver $imageUrlResolver)
    {
        $this->imageUrlResolver = $imageUrlResolver;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function transform(array $productItems, array $attributes): array
    {
        foreach ($productItems as &$item) {
            foreach ($attributes as $attributeName => $outputAttributes) {
                $rawValue = $item[$attributeName] ?? '';
                $item[$attributeName] = [];

                if (\in_array('url', $outputAttributes, true)) {
                    $item[$attributeName]['url'] = $this->imageUrlResolver->resolve($rawValue, $attributeName);
                }
                if (\in_array('label', $outputAttributes, true)) {
                    $item[$attributeName]['label'] = $item[$attributeName . '_label'] ?? $item['name'] ?? '';
                }
            }
        }

        return $productItems;
    }
}
