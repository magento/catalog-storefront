<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Model\Storage\Client;

/**
 * Connection pull.
 */
class ConnectionPull
{
    /**
     * @var \Elasticsearch\Client[]
     */
    private $connectionPull;

    /**
     * @var Config
     */
    private $config;

    /**
     * Initialize Elasticsearch Client
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get Elasticsearch connection.
     *
     * @return \Elasticsearch\Client
     */
    public function getConnection()
    {
        $pid = getmypid();
        if (!isset($this->client[$pid])) {
            $config = $this->config->buildConfig();
            $this->connectionPull[$pid] = \Elasticsearch\ClientBuilder::fromConfig($config, true);
        }
        return $this->connectionPull[$pid];
    }
}
