<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage\Client;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Client Config.
 *
 * Temporal solution to handle configurations for storage while catalog storefront application is a part
 * of Magento monolith.
 * This Class contains a few hard-codes and rely on a strict and "hidden" structure of connection configuration.
 *
 * For now connection is setup during the installation of Magento application
 * @see \Magento\CatalogProduct\Setup\Recurring
 *
 * Example that we hard-code during installation of Magento:
 * 'catalog-store-front' => [
 *     'connections' => [
 *         'default' => [
 *             'protocol' => 'http',
 *             'hostname' => 'localhost',
 *             'port' => '9200',
 *             'username' => '',
 *             'password' => '',
 *             'timeout' => 3
 *         ]
 *     ],
 *     'timeout' => 60,
 *     'alias_name' => 'catalog_storefront',
 *     'source_prefix' => 'catalog_storefront_v',
 *     'source_current_version' => 1
 * ]
 *
 * For now you only has to follow the structure and modify value of each option to appropriate that correspond to
 * your environment. It's possible by particularly modify the app/etc/env.php file that is the representation
 * of connection configuration in the Magento application.
 *
 * TODO: MC-29894
 */
class Config
{
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
    private $connectionConfig;

    /**
     * @var array
     */
    private $clientOptions;

    /**
     * Initialize Elasticsearch Client
     *
     * @param Reader $configReader
     * @throws ConfigurationMismatchException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function __construct(Reader $configReader)
    {
        $configData = $configReader->load(ConfigFilePool::APP_ENV)['catalog-store-front'];
        $options = $configData['connections']['default'];

        if (empty($options['hostname']) || ((!empty($options['enableAuth'])
                    && ($options['enableAuth'] == 1)) && (empty($options['username']) || empty($options['password'])))
        ) {
            throw new ConfigurationMismatchException(
                __('The search failed because of a search engine misconfiguration.')
            );
        }
        $this->connectionConfig = $options;
        $this->clientOptions = $configData;
    }

    /**
     * Return config of the Client.
     *
     * @return array
     */
    public function getClientConfig()
    {
        return $this->clientOptions;
    }

    /**
     * Get max children for complex entries.
     *
     * @param string $entityType
     * @return int
     */
    public function getMaxChildren(string $entityType): int
    {
        return isset($this->clientOptions[$entityType]['max_children'])
            ? $this->clientOptions[$entityType]['max_children']
            : self::MAX_CHILDREN;
    }

    /**
     * Get join field.
     *
     * @param string $entityType
     * @return string
     */
    public function getJoinField(string $entityType): string
    {
        return isset($this->clientOptions[$entityType]['join_field'])
            ? $this->clientOptions[$entityType]['join_field']
            : self::JOIN_FIELD;
    }

    /**
     * Get parent key.
     *
     * @param string $entityType
     * @return string
     */
    public function getParentKey(string $entityType): string
    {
        return isset($this->clientOptions[$entityType]['parent_key'])
            ? $this->clientOptions[$entityType]['parent_key']
            : self::PARENT_KEY;
    }

    /**
     * Get child key.
     *
     * @param string $entityType
     * @return string
     */
    public function getChildKey(string $entityType): string
    {
        return isset($this->clientOptions[$entityType]['child_key'])
            ? $this->clientOptions[$entityType]['child_key']
            : self::CHILD_KEY;
    }

    /**
     * Return connection config of the Client.
     *
     * @return array
     */
    public function getConnectionConfig()
    {
        return $this->connectionConfig;
    }

    /**
     * Build config.
     *
     * @return array
     */
    public function buildConfig()
    {
        $portString = '';
        if (!empty($this->connectionConfig['port'])) {
            $portString = ':' . $this->connectionConfig['port'];
        }

        $host = $this->connectionConfig['protocol'] . '://' . $this->connectionConfig['hostname'] . $portString;

        $result['hosts'] = [$host];

        return $result;
    }
}
