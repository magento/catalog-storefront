<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: catalog/catalog.proto

namespace Magento\CatalogStorefrontApi\Proto;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>magento.catalogStorefrontApi.proto.Image</code>
 */
class Image extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.magento.catalogStorefrontApi.proto.MediaResource resource = 1;</code>
     */
    protected $resource = null;
    /**
     * Generated from protobuf field <code>string sort_order = 2;</code>
     */
    protected $sort_order = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Magento\CatalogStorefrontApi\Proto\MediaResource $resource
     *     @type string $sort_order
     * }
     */
    public function __construct($data = null)
    {
        \Magento\CatalogStorefrontApi\Metadata\Catalog::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.magento.catalogStorefrontApi.proto.MediaResource resource = 1;</code>
     * @return \Magento\CatalogStorefrontApi\Proto\MediaResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Generated from protobuf field <code>.magento.catalogStorefrontApi.proto.MediaResource resource = 1;</code>
     * @param \Magento\CatalogStorefrontApi\Proto\MediaResource $var
     * @return $this
     */
    public function setResource($var)
    {
        GPBUtil::checkMessage($var, \Magento\CatalogStorefrontApi\Proto\MediaResource::class);
        $this->resource = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string sort_order = 2;</code>
     * @return string
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * Generated from protobuf field <code>string sort_order = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setSortOrder($var)
    {
        GPBUtil::checkString($var, true);
        $this->sort_order = $var;

        return $this;
    }
}
