<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase;

class WebApiHelper extends WebapiAbstract
{
    /**
     * Perform Web API call to the system under test.
     *
     * @see \Magento\TestFramework\TestCase\Webapi\AdapterInterface::call()
     * @param array $serviceInfo
     * @param array $arguments
     * @param string|null $webApiAdapterCode
     * @param string|null $storeCode
     * @param \Magento\Integration\Model\Integration|null $integration
     * @return array|int|string|float|bool Web API call results
     */
    public function _webApiCall(
        $serviceInfo,
        $arguments = [],
        $webApiAdapterCode = null,
        $storeCode = null,
        $integration = null
    ) {
        return parent::_webApiCall($serviceInfo, $arguments, $webApiAdapterCode, $storeCode, $integration);
    }
}
