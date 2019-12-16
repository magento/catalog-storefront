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

                $item[$attributeName]['url'] = $this->imageUrlResolver->resolve($rawValue, $attributeName);
                $item[$attributeName]['label'] = $item[$attributeName . '_label'] ?? $item['name'] ?? '';
            }
        }

        return $productItems;
    }
}
