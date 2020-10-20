<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsStorefront\Model\Storage\Client\Config;

use Magento\CatalogStorefront\Model\Storage\Client\Config\EntityConfigInterface;

/**
 * Review entity type config.
 */
class Review implements EntityConfigInterface
{
    /**
     * Entity name. Used to hold configuration for specific entity type and as a part of the storage name
     */
    public const ENTITY_NAME = 'review';

    /**
     * @inheritdoc
     */
    public function getSettings(): array
    {
        return [
            'dynamic_templates' => [
                [
                    'default_mapping' => [
                        'match' => '*',
                        'match_mapping_type' => '*',
                        'mapping' => [
                            'index' => false,
                        ],
                    ],
                ]
            ],
        ];
    }
}
