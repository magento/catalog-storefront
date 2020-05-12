<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// *
// Copyright © Magento, Inc. All rights reserved.
// See COPYING.txt for license details.
namespace Magento\CatalogStorefrontApi\Proto;

/**
 */
class CatalogClient extends \Grpc\BaseStub
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
     * @param \Magento\CatalogStorefrontApi\Proto\ProductsGetRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetProducts(
        \Magento\CatalogStorefrontApi\Proto\ProductsGetRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.Catalog/GetProducts',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ProductsGetResult', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\ImportProductsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function ImportProducts(
        \Magento\CatalogStorefrontApi\Proto\ImportProductsRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.Catalog/ImportProducts',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ImportProductsResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\CategoriesGetRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetCategories(
        \Magento\CatalogStorefrontApi\Proto\CategoriesGetRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.Catalog/GetCategories',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\CategoriesGetResponse', 'decode'],
            $metadata,
            $options
        );
    }
}
