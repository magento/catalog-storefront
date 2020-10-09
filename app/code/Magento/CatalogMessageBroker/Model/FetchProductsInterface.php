<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model;

use Magento\CatalogExport\Event\Data\Entity;

/**
 * Fetch product data
 */
interface FetchProductsInterface
{
    /**
     * Fetch product data
     *
     * @param Entity[] $entities
     * @param string $scope
     *
     * @return array
     */
    public function execute(array $entities, string $scope): array;
}
