<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// *
// Copyright © Magento, Inc. All rights reserved.
// See COPYING.txt for license details.
namespace Magento\CatalogStorefrontApi\Proto;

/**
 */
class RatingsMetadataClient extends \Grpc\BaseStub
{

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null)
    {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\ImportRatingsMetadataRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Magento\CatalogStorefrontApi\Proto\ImportRatingsMetadataResponse
     */
    public function ImportRatingsMetadata(
        \Magento\CatalogStorefrontApi\Proto\ImportRatingsMetadataRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.RatingsMetadata/ImportRatingsMetadata',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ImportRatingsMetadataResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\DeleteRatingsMetadataRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Magento\CatalogStorefrontApi\Proto\DeleteRatingsMetadataResponse
     */
    public function DeleteRatingsMetadata(
        \Magento\CatalogStorefrontApi\Proto\DeleteRatingsMetadataRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.RatingsMetadata/DeleteRatingsMetadata',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\DeleteRatingsMetadataResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\RatingsMetadataRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Magento\CatalogStorefrontApi\Proto\RatingsMetadataResponse
     */
    public function GetRatingsMetadata(
        \Magento\CatalogStorefrontApi\Proto\RatingsMetadataRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.RatingsMetadata/GetRatingsMetadata',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\RatingsMetadataResponse', 'decode'],
            $metadata,
            $options
        );
    }
}
