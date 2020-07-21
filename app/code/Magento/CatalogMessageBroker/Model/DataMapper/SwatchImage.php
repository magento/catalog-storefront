<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

use Magento\Framework\File\Uploader;

/**
 * Data mapper for product swatch image
 */
class SwatchImage implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(array $data): array
    {
        if (empty($data['swatch_image'])) {
            return [];
        }

        $baseName = \basename($data['swatch_image']['url']);

        return [
            'swatch_image' => \sprintf('%s/%s', Uploader::getDispersionPath($baseName), $baseName),
        ];
    }
}
