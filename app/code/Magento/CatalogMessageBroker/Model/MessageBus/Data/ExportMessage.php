<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Data;

/**
 * Structure of message processed by catalog storefront message broker
 * TODO: Change name?
 */
class ExportMessage implements ExportMessageInterface
{
    /**
     * @var DataInterface
     */
    private $data;

    /**
     * @var MetaInterface
     */
    private $meta;

    /**
     * @param MetaInterface $meta
     * @param DataInterface $data
     */
    public function __construct(MetaInterface $meta, DataInterface $data)
    {
        $this->meta = $meta;
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function getMeta(): MetaInterface
    {
        return $this->meta;
    }

    /**
     * @inheritdoc
     */
    public function getData(): DataInterface
    {
        return $this->data;
    }
}
