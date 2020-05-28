<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ServicesConnector\Api;

/**
 * Provides configured http client for communication with Magento services
 *
 * `production` and `sandbox` are only two types of supported environments
 */
interface ClientResolverInterface
{
    /**
     * Provides a configured HTTP client
     *
     * The client points to api gateway instance, so you need to pass only service specific chunks in URL
     * E.g. https://api.magento.com/service/service_path
     *      \_____predefined______/
     *
     * The client also adds authentication headers(api keys) to every applicable HTTP request
     *
     * @param string $extension
     * @param string $environment (production|sandbox)
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Client
     */
    public function createHttpClient($extension, $environment = 'production');
}
