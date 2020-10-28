<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: catalog/catalog.proto

namespace Magento\CatalogStorefrontApi\Proto;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>magento.catalogStorefrontApi.proto.DeleteVariantsRequest</code>
 */
class DeleteVariantsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * list of ProductVariant.id
     *
     * Generated from protobuf field <code>repeated string id = 1;</code>
     */
    private $id;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $id
     *           list of ProductVariant.id
     * }
     */
    public function __construct($data = null)
    {
        \Magento\CatalogStorefrontApi\Metadata\Catalog::initOnce();
        parent::__construct($data);
    }

    /**
     * list of ProductVariant.id
     *
     * Generated from protobuf field <code>repeated string id = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * list of ProductVariant.id
     *
     * Generated from protobuf field <code>repeated string id = 1;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setId($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->id = $arr;

        return $this;
    }
}
