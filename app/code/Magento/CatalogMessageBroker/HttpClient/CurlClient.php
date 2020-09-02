<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\HttpClient;

/**
 * Generic cURL client wrapper for get request
 * TODO: ad-hoc solution. replace with some ready-to-use library
 */
class CurlClient
{
    /**
     * Perform a HTTP GET request and return the full response
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    public function get($url, $data = [], $headers = []): array
    {
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $curlOpts = [];
        $curlOpts[CURLOPT_CUSTOMREQUEST] = \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET;
        return $this->invokeApi($url, $curlOpts, $headers);
    }

    /**
     * Makes the REST api call using passed $curl object
     *
     * @param string $url
     * @param array $additionalCurlOpts cURL Options
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    private function invokeApi($url, $additionalCurlOpts, $headers = []): array
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $curl = curl_init($url);
        if ($curl === false) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Error Initializing cURL for baseUrl: ' . $url);
        }

        $curlOpts = $this->getCurlOptions($additionalCurlOpts, $headers);

        foreach ($curlOpts as $opt => $val) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            curl_setopt($curl, $opt, $val);
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $response = curl_exec($curl);
        if ($response === false) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $error = curl_error($curl);
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception($error);
        }

        $resp = [];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $resp['header'] = substr($response, 0, $headerSize);
        $resp['body'] = substr($response, $headerSize);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $resp['meta'] = curl_getinfo($curl);
        if ($resp['meta'] === false) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $error = curl_error($curl);
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception($error);
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_close($curl);

        $meta = $resp['meta'];
        if ($meta && $meta['http_code'] >= 400) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception($resp['body'], $meta['http_code']);
        }

        return $resp;
    }

    /**
     * Constructs and returns a curl options array
     *
     * @param array $customCurlOpts Additional / overridden cURL options
     * @param array $headers
     * @return array
     */
    private function getCurlOptions($customCurlOpts = [], $headers = []): array
    {
        // default curl options
        $curlOpts = [
            CURLOPT_RETURNTRANSFER => true, // return result instead of echoing
            CURLOPT_FOLLOWLOCATION => false, // follow redirects, Location: headers
            CURLOPT_MAXREDIRS => 10, // but don't redirect more than 10 times
            CURLOPT_HTTPHEADER => [],
            CURLOPT_HEADER => 1,
        ];

        // merge headers
        $headers = array_merge($curlOpts[CURLOPT_HTTPHEADER], $headers);

        $curlOpts[CURLOPT_HTTPHEADER] = $headers;

        // merge custom Curl Options & return
        foreach ($customCurlOpts as $opt => $val) {
            $curlOpts[$opt] = $val;
        }

        return $curlOpts;
    }
}
