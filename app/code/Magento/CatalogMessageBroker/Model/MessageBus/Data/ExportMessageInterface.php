<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Data;

/**
 * Structure of message processed by catalog storefront message broker
 */
interface ExportMessageInterface
{
    /**
     * Get changed entities metadata
     *
     * @return \Magento\CatalogMessageBroker\Model\MessageBus\Data\MetaInterface
     */
    public function getMeta(): MetaInterface;

    /**
     * Get changed entities data
     *
     * @return \Magento\CatalogMessageBroker\Model\MessageBus\Data\DataInterface
     */
    public function getData(): DataInterface;
}
