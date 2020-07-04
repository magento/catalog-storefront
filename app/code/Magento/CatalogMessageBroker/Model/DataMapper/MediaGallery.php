<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\DataMapper;

/**
 * Class MediaGallery
 */
class MediaGallery implements DataMapperInterface
{
    /**
     * @inheritDoc
     */
    public function map(array $data): array
    {
        if (empty($data['media_gallery'])) {
            return [];
        }

        return \array_map(function ($mediaData) {
            return \array_merge([
                'url' => $mediaData['url'],
                'label' => $mediaData['label'],
                'position' => $mediaData['sort_order'],
                'media_type' => $mediaData['media_type'],
            ], isset($mediaData['video_attributes']) && !empty($mediaData['video_attributes']) ? [
                'video_content' => $mediaData['video_attributes'],
            ] : []);
        }, $data['media_gallery']);
    }
}
