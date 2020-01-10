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
     * Product image attributes.
     */
    private const IMAGE_ATTRIBUTES = [
        'url',
        'label',
    ];

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
        $attributeName = key($attributes);
        $fields = current($attributes);

        if (empty($fields) || (\is_string($fields) && ($attributeName === $fields))) {
            $fields = self::IMAGE_ATTRIBUTES;
        }

        foreach ($productItems as &$item) {
            $rawValue = $item[$attributeName] ?? '';
            $item[$attributeName] = [];

            if (\in_array('url', $fields, true)) {
                $item[$attributeName]['url'] = $this->imageUrlResolver->resolve($rawValue, $attributeName);
            }
            if (\in_array('label', $fields, true)) {
                $item[$attributeName]['label'] = $item[$attributeName . '_label'] ?? $item['name'] ?? '';
            }
        }

        return $productItems;
    }
}
