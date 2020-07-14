<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExportApi\Api\VariantRepositoryInterface;

/**
 * Default implementation of product variants repository.
 */
class VariantRepository implements VariantRepositoryInterface
{
    /**
     * Constant value for setting max items in response.
     */
    private const MAX_ITEMS_IN_RESPONSE = 250;

    /**
     * @inheritDoc
     */
    public function get(array $ids): array
    {
        // TODO: Implement get() method.
    }
}
