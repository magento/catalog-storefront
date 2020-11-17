<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage;

use Magento\CatalogStorefront\Model\Storage\Client\Config;

/**
 * State represents the current metadata information of Storage.
 */
class State
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get current alias name of storage.
     *
     * @return string
     */
    public function getAliasName(): string
    {
        return $this->config->getAliasName();
    }

    /**
     * Get current data source name of storage taking into account version of the data source.
     *
     * TODO: Adapt to work without store code https://github.com/magento/catalog-storefront/issues/417
     *
     * @param array $scopes
     * @return string
     */
    public function getCurrentDataSourceName(array $scopes): string
    {
        return $this->config->getSourcePrefix() . $this->config->getCurrentSourceVersion() . '_'
            . \implode('_', $scopes);
    }

    /**
     * Generate the new version of name for data source based on the current state.
     *
     * @return string
     */
    public function generateNewDataSourceName(): string
    {
        return $this->config->getSourcePrefix() . ($this->config->getCurrentSourceVersion() + 1);
    }
}
