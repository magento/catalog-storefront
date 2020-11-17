<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExport\Event\Data\Entity;

/**
 * Fetch product variants data
 */
interface FetchProductVariantsInterface
{
    /**
     * Fetch product variants data
     *
     * @param Entity[] $entities
     *
     * @return array
     */
    public function execute(array $entities): array;
}
