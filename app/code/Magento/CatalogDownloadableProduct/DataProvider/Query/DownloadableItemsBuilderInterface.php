<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogDownloadableProduct\DataProvider\Query;

use Magento\Framework\DB\Select;

/**
 * Builder for downloadable product items.
 */
interface DownloadableItemsBuilderInterface
{
    /**
     * Build query to get downloadable product items.
     *
     * @param array $productIds
     * @param array $attributes
     * @param int $storeId
     * @return Select
     */
    public function build(array $productIds, array $attributes, int $storeId): Select;
}
