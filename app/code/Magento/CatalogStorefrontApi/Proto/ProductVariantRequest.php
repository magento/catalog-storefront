<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: catalog.proto

namespace Magento\CatalogStorefrontApi\Proto;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>magento.catalogStorefrontApi.proto.ProductVariantRequest</code>
 */
class ProductVariantRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string product_Id = 1;</code>
     */
    protected $product_Id = '';
    /**
     * Generated from protobuf field <code>string store = 2;</code>
     */
    protected $store = '';
    /**
     * Generated from protobuf field <code>repeated .magento.catalogStorefrontApi.proto.PaginationRequest pagination = 3;</code>
     */
    private $pagination;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $product_Id
     *     @type string $store
     *     @type \Magento\CatalogStorefrontApi\Proto\PaginationRequest[]|\Google\Protobuf\Internal\RepeatedField $pagination
     * }
     */
    public function __construct($data = null)
    {
        \Magento\CatalogStorefrontApi\Metadata\Catalog::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string product_Id = 1;</code>
     * @return string
     */
    public function getProductId()
    {
        return $this->product_Id;
    }

    /**
     * Generated from protobuf field <code>string product_Id = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setProductId($var)
    {
        GPBUtil::checkString($var, true);
        $this->product_Id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string store = 2;</code>
     * @return string
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Generated from protobuf field <code>string store = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setStore($var)
    {
        GPBUtil::checkString($var, true);
        $this->store = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated .magento.catalogStorefrontApi.proto.PaginationRequest pagination = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Generated from protobuf field <code>repeated .magento.catalogStorefrontApi.proto.PaginationRequest pagination = 3;</code>
     * @param \Magento\CatalogStorefrontApi\Proto\PaginationRequest[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPagination($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Magento\CatalogStorefrontApi\Proto\PaginationRequest::class);
        $this->pagination = $arr;

        return $this;
    }
}
