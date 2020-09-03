<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\HttpClient;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CurlClient $curlClient
     * @param Json $jsonSerializer
     * @param UrlInterface $url
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurlClient $curlClient,
        Json $jsonSerializer,
        UrlInterface $url,
        LoggerInterface $logger
    ) {
        $this->curlClient = $curlClient;
        $this->jsonSerializer = $jsonSerializer;
        $this->url = $url;
        $this->logger = $logger;
    }

    /**
     * Perform HTTP GET request
     *
     * @param string $resourcePath Resource URL like /V1/Resource1/123
     * @param array $data
     * @param array $headers
     * @return array
     * @throws \Throwable
     */
    public function get($resourcePath, $data = [], $headers = [])
    {
        $url = $this->constructResourceUrl($resourcePath);
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        try {
            $responseBody = $this->curlClient->get($url, $data, $headers);
            return !empty($responseBody['body']) ? $this->jsonSerializer->unserialize($responseBody['body']) : [];
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Error during REST call to Export API: url: %s, response: %s, response_code: %s',
                    $url,
                    $responseBody['body'] ?? '',
                    $responseBody['http_code'] ?? ''
                ),
                ['exception' => $e]
            );
            throw $e;
        }
    }

    /**
     * Construct given resource url
     *
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
