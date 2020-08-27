<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\HttpClient;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;

/**
 * Client for invoking REST API
 * TODO: ad-hoc solution. replace with some ready-to-use library
 */
class RestClient
{
    /**
     * @var string REST URL base path
     */
    private $restBasePath = '/rest/';

    /**
     * @var CurlClient
     */
    private $curlClient;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param CurlClient $curlClient
     * @param Json $jsonSerializer
     * @param UrlInterface $url
     */
    public function __construct(
        CurlClient $curlClient,
        Json $jsonSerializer,
        UrlInterface $url
    ) {
        $this->curlClient = $curlClient;
        $this->jsonSerializer = $jsonSerializer;
        $this->url = $url;
    }

    /**
     * Perform HTTP GET request
     *
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @param array $data
     * @param array $headers
     * @return mixed
     * @throws \Exception
     */
    public function get($resourcePath, $data = [], $headers = [])
    {
        $url = $this->constructResourceUrl($resourcePath);
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $responseBody = $this->curlClient->get($url, $data, $headers);
        // TODO: handle errors
        return $this->jsonSerializer->unserialize($responseBody['body'] ?? '');
    }

    /**
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @return string resource URL
     */
    private function constructResourceUrl($resourcePath): string
    {
        // TODO: for test purposes only. base URL of "Export API" should be retrieved from configuration/ or from event
        $storefrontAppHost = $this->url->getBaseUrl();
        return rtrim($storefrontAppHost, '/') . $this->restBasePath . ltrim($resourcePath, '/');
    }
}
