<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: catalog.proto

namespace Magento\CatalogStorefrontApi\Proto;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>magento.catalogStorefrontApi.proto.ProductReviewCountResponse</code>
 */
class ProductReviewCountResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>int32 review_count = 1;</code>
     */
    protected $review_count = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $review_count
     * }
     */
    public function __construct($data = null)
    {
        \Magento\CatalogStorefrontApi\Metadata\Catalog::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int32 review_count = 1;</code>
     * @return int
     */
    public function getReviewCount()
    {
        return $this->review_count;
    }

    /**
     * Generated from protobuf field <code>int32 review_count = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setReviewCount($var)
    {
        GPBUtil::checkInt32($var);
        $this->review_count = $var;

        return $this;
    }
}