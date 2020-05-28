<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ServicesConnector\Api;

/**
 * Provides configuration settings useful for other modules
 */
interface ConfigInterface
{
    /**
     * Returns keys configuration page URL
     *
     * @param string $extension
     * @param string $environment (production|sandbox)
     * @return string
     */
    public function getKeyConfigPage($extension, $environment = 'production');

    /**
     * Return api portal url
     *
     * @return string
     */
    public function getApiPortalUrl();
}
