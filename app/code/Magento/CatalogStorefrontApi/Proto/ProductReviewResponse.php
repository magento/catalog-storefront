<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: catalog.proto

namespace Magento\CatalogStorefrontApi\Proto;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>magento.catalogStorefrontApi.proto.ProductReviewResponse</code>
 */
class ProductReviewResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .magento.catalogStorefrontApi.proto.ReadReview items = 1;</code>
     */
    private $items;
    /**
     * Generated from protobuf field <code>.magento.catalogStorefrontApi.proto.PaginationResponse pagination = 2;</code>
     */
    protected $pagination = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Magento\CatalogStorefrontApi\Proto\ReadReview[]|\Google\Protobuf\Internal\RepeatedField $items
     *     @type \Magento\CatalogStorefrontApi\Proto\PaginationResponse $pagination
     * }
     */
    public function __construct($data = null)
    {
        \Magento\CatalogStorefrontApi\Metadata\Catalog::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .magento.catalogStorefrontApi.proto.ReadReview items = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Generated from protobuf field <code>repeated .magento.catalogStorefrontApi.proto.ReadReview items = 1;</code>
     * @param \Magento\CatalogStorefrontApi\Proto\ReadReview[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setItems($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Magento\CatalogStorefrontApi\Proto\ReadReview::class);
        $this->items = $arr;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.magento.catalogStorefrontApi.proto.PaginationResponse pagination = 2;</code>
     * @return \Magento\CatalogStorefrontApi\Proto\PaginationResponse
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Generated from protobuf field <code>.magento.catalogStorefrontApi.proto.PaginationResponse pagination = 2;</code>
     * @param \Magento\CatalogStorefrontApi\Proto\PaginationResponse $var
     * @return $this
     */
    public function setPagination($var)
    {
        GPBUtil::checkMessage($var, \Magento\CatalogStorefrontApi\Proto\PaginationResponse::class);
        $this->pagination = $var;

        return $this;
    }
}
