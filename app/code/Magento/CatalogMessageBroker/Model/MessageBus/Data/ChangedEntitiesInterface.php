<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\Data;

/**
 * Changed entities object interface
 */
interface ChangedEntitiesInterface
{
    /**
     * Get changed entities metadata
     *
     * @return \Magento\CatalogExport\Model\Data\MetaInterface
     */
    public function getMeta(): MetaInterface;

    /**
     * Get changed entities data
     *
     * @return \Magento\CatalogExport\Model\Data\DataInterface
     */
    public function getData(): DataInterface;
}
