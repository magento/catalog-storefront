<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client\Config;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Product variant entity type config.
 */
class ProductVariant implements EntityConfigInterface
{
    /**
     * Entity name. Used to hold configuration for specific entity type and as a part of the storage name
     */
    public const ENTITY_NAME = 'product_variant';

    /**
     * @inheritdoc
     */
    public function getSettings() : array
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
            'properties' => [
                'id' => [
                    'type' => 'keyword'
                ],
                'option_value' => [
                    'type' => 'keyword',
                ],
                'parent_id' => [
                    'type' => 'keyword',
                ],
                'product_id' => [
                    'type' => 'keyword',
                ],
            ]
        ];
    }
}
