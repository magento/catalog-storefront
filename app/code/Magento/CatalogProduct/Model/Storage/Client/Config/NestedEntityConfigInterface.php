<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Client\Config;

/**
 * Config of nested entity.
 *
 * This specific entity type config if depicts the 1 depth tree structure with parent-children relations.
 */
interface NestedEntityConfigInterface extends EntityConfigInterface
{
    /**
     * Get max children for complex entries.
     *
     * @return int
     */
    public function getMaxChildren(): int;

    /**
     * Get join field.
     *
     * @return string
     */
    public function getJoinField(): string;

    /**
     * Get parent key.
     *
     * @return string
     */
    public function getParentKey(): string;

    /**
     * Get child key.
     *
     * @return string
     */
    public function getChildKey(): string;
}
