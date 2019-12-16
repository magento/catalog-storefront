<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider;

use Magento\Framework\ObjectManagerInterface;

/**
 * @inheritdoc
 */
class Transformer implements TransformerInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $map;

    /**
     * @param array $map
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        array $map,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->map = $map;
    }

    /**
     * @inheritdoc
     */
    public function transform(array $productItems, array $attributes): array
    {
        foreach ($this->map as $attributeName => $transformerClass) {
            $attributeTransformer = $this->objectManager->get($transformerClass);
            if (!$attributeTransformer instanceof TransformerInterface) {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Data Transformer "%s" must implement %s',
                        $transformerClass,
                        TransformerInterface::class
                    )
                );
            }
            // TODO: handle ad-hoc solution MC-29791
            if (empty($attributes)) {
                $outputAttributes = $attributeName;
            } else {
                $outputAttributes = $attributes[$attributeName] ?? null;
            }
            if ($outputAttributes === null) {
                $index = \array_search($attributeName, $attributes, true);
                $outputAttributes = $index !== false ? $attributes[$index] : null;
            }
            if (!$outputAttributes) {
                continue;
            }
            $outputAttributes = [$attributeName => $outputAttributes];
            $productItems = $attributeTransformer->transform($productItems, $outputAttributes);
        }

        return $productItems;
    }
}
