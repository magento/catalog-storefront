<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: catalog.proto

namespace Magento\CatalogStorefrontApi\Proto;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>magento.catalogStorefrontApi.proto.Rating</code>
 */
class Rating extends \Google\Protobuf\Internal\Message
{
    /**
     * Base64 encoded rating ID
     *
     * Generated from protobuf field <code>string rating_id = 1;</code>
     */
    protected $rating_id = '';
    /**
     * Rating Value
     *
     * Generated from protobuf field <code>string value = 2;</code>
     */
    protected $value = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $rating_id
     *           Base64 encoded rating ID
     *     @type string $value
     *           Rating Value
     * }
     */
    public function __construct($data = null)
    {
        \Magento\CatalogStorefrontApi\Metadata\Catalog::initOnce();
        parent::__construct($data);
    }

    /**
     * Base64 encoded rating ID
     *
     * Generated from protobuf field <code>string rating_id = 1;</code>
     * @return string
     */
    public function getRatingId()
    {
        return $this->rating_id;
    }

    /**
     * Base64 encoded rating ID
     *
     * Generated from protobuf field <code>string rating_id = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setRatingId($var)
    {
        GPBUtil::checkString($var, true);
        $this->rating_id = $var;

        return $this;
    }

    /**
     * Rating Value
     *
     * Generated from protobuf field <code>string value = 2;</code>
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Rating Value
     *
     * Generated from protobuf field <code>string value = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setValue($var)
    {
        GPBUtil::checkString($var, true);
        $this->value = $var;

        return $this;
    }
}
