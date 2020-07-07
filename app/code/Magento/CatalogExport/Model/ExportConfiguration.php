<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\Framework\App\DeploymentConfig;

class ExportConfiguration
{
    /**
     * Constant value for setting max items in response
     */
    private const MAX_ITEMS_IN_RESPONSE = 250;

    /**
     * Max items in response path in app/etc/config.php|env.php
     */
    private const MAX_ITEM_IN_RESPONSE_PATH = 'catalog_export/max_items_in_response';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * ExportConfiguration constructor.
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Get max items in response
     *
     * @return int
     */
    public function getMaxItemsInResponse()
    {
        $maxItemsInResponse = (int) $this->deploymentConfig->get(self::MAX_ITEM_IN_RESPONSE_PATH);
        return $maxItemsInResponse ?: self::MAX_ITEMS_IN_RESPONSE;
    }
}
