<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// *
// Copyright © Magento, Inc. All rights reserved.
// See COPYING.txt for license details.
namespace Magento\CatalogStorefrontApi\Proto;

/**
 */
class VariantServiceClient extends \Grpc\BaseStub
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
     * @param \Magento\CatalogStorefrontApi\Proto\ImportVariantsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Magento\CatalogStorefrontApi\Proto\ImportVariantsResponse
     */
    public function ImportProductVariants(
        \Magento\CatalogStorefrontApi\Proto\ImportVariantsRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.VariantService/ImportProductVariants',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ImportVariantsResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\DeleteVariantsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Magento\CatalogStorefrontApi\Proto\DeleteVariantsResponse
     */
    public function DeleteProductVariants(
        \Magento\CatalogStorefrontApi\Proto\DeleteVariantsRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.VariantService/DeleteProductVariants',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\DeleteVariantsResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * get all variants that belong to a product
     * @param \Magento\CatalogStorefrontApi\Proto\ProductVariantRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Magento\CatalogStorefrontApi\Proto\ProductVariantResponse
     */
    public function GetProductVariants(
        \Magento\CatalogStorefrontApi\Proto\ProductVariantRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.VariantService/GetProductVariants',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ProductVariantResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * match the variants which correspond, and do not contradict, the merchant selection (%like operation)
     * @param \Magento\CatalogStorefrontApi\Proto\OptionSelectionRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Magento\CatalogStorefrontApi\Proto\ProductVariantResponse
     */
    public function GetVariantsMatch(
        \Magento\CatalogStorefrontApi\Proto\OptionSelectionRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.VariantService/GetVariantsMatch',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ProductVariantResponse', 'decode'],
            $metadata,
            $options
        );
    }
}
