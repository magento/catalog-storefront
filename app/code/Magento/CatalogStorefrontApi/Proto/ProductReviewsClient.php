<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// *
// Copyright © Magento, Inc. All rights reserved.
// See COPYING.txt for license details.
namespace Magento\CatalogStorefrontApi\Proto;

/**
 */
class ProductReviewsClient extends \Grpc\BaseStub
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
     * @param \Magento\CatalogStorefrontApi\Proto\ImportReviewsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function ImportProductReviews(
        \Magento\CatalogStorefrontApi\Proto\ImportReviewsRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.ProductReviews/ImportProductReviews',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ImportReviewsResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\DeleteReviewsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function DeleteProductReviews(
        \Magento\CatalogStorefrontApi\Proto\DeleteReviewsRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.ProductReviews/DeleteProductReviews',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\DeleteReviewsResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\ProductReviewRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetProductReviews(
        \Magento\CatalogStorefrontApi\Proto\ProductReviewRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.ProductReviews/GetProductReviews',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ProductReviewResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\CustomerProductReviewRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetCustomerProductReviews(
        \Magento\CatalogStorefrontApi\Proto\CustomerProductReviewRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.ProductReviews/GetCustomerProductReviews',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\CustomerProductReviewResponse', 'decode'],
            $metadata,
            $options
        );
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Proto\ProductReviewCountRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetProductReviewCount(
        \Magento\CatalogStorefrontApi\Proto\ProductReviewCountRequest $argument,
        $metadata = [],
        $options = []
    )
    {
        return $this->_simpleRequest(
            '/magento.catalogStorefrontApi.proto.ProductReviews/GetProductReviewCount',
            $argument,
            ['\Magento\CatalogStorefrontApi\Proto\ProductReviewCountResponse', 'decode'],
            $metadata,
            $options
        );
    }
}
