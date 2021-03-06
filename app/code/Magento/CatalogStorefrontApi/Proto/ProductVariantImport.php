<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: catalog.proto

namespace Magento\CatalogStorefrontApi\Proto;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>magento.catalogStorefrontApi.proto.ProductVariantImport</code>
 */
class ProductVariantImport extends \Google\Protobuf\Internal\Message
{
    /**
     * variant identifier following the convention :prefix:/:parentId:/:entityId:
     *
     * Generated from protobuf field <code>string id = 1;</code>
     */
    protected $id = '';
    /**
     * list of option values intersections, which represent this variant, parent_id:option_id/optionValue.uid
     *
     * Generated from protobuf field <code>repeated string option_values = 2;</code>
     */
    private $option_values;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $id
     *           variant identifier following the convention :prefix:/:parentId:/:entityId:
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $option_values
     *           list of option values intersections, which represent this variant, parent_id:option_id/optionValue.uid
     * }
     */
    public function __construct($data = null)
    {
        \Magento\CatalogStorefrontApi\Metadata\Catalog::initOnce();
        parent::__construct($data);
    }

    /**
     * variant identifier following the convention :prefix:/:parentId:/:entityId:
     *
     * Generated from protobuf field <code>string id = 1;</code>
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * variant identifier following the convention :prefix:/:parentId:/:entityId:
     *
     * Generated from protobuf field <code>string id = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setId($var)
    {
        GPBUtil::checkString($var, true);
        $this->id = $var;

        return $this;
    }

    /**
     * list of option values intersections, which represent this variant, parent_id:option_id/optionValue.uid
     *
     * Generated from protobuf field <code>repeated string option_values = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getOptionValues()
    {
        return $this->option_values;
    }

    /**
     * list of option values intersections, which represent this variant, parent_id:option_id/optionValue.uid
     *
     * Generated from protobuf field <code>repeated string option_values = 2;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setOptionValues($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->option_values = $arr;

        return $this;
    }
}
