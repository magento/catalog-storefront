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
 * Category entity type config.
 */
class Category implements NestedEntityConfigInterface
{
    /**
     * Entity name. Used to hold configuration for specific entity type and as a part of the storage name
     */
    public const ENTITY_NAME = 'category';

    /**#@+
     * Text flags for Elasticsearch relation actions.
     */
    private const CHILD_KEY = 'variant';
    private const PARENT_KEY = 'complex';
    private const JOIN_FIELD = 'parent_id';
    private const MAX_CHILDREN = 100;
    /**#@-*/

    /**
     * @var array
     */
    private $clientOptions;

    /**
     * @param Reader $configReader
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function __construct(Reader $configReader)
    {
        $configData = $configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        $this->clientOptions = $configData;
    }

    /**
     * @inheritdoc
     */
    public function getSettings() : array
    {
        return [
            'properties' => [
                $this->getJoinField() => [
                    'type' => 'join',
                    'relations' => [
                        $this->getParentKey() => $this->getChildKey()
                    ]
                ],
            ],
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

    /**
     * @inheritdoc
     */
    public function getMaxChildren(): int
    {
        return $this->clientOptions[self::ENTITY_NAME]['max_children'] ?? self::MAX_CHILDREN;
    }

    /**
     * @inheritdoc
     */
    public function getJoinField(): string
    {
        return $this->clientOptions[self::ENTITY_NAME]['join_field'] ?? self::JOIN_FIELD;
    }

    /**
     * @inheritdoc
     */
    public function getParentKey(): string
    {
        return $this->clientOptions[self::ENTITY_NAME]['parent_key'] ?? self::PARENT_KEY;
    }

    /**
     * @inheritdoc
     */
    public function getChildKey(): string
    {
        return $this->clientOptions[self::ENTITY_NAME]['child_key'] ?? self::CHILD_KEY;
    }
}
