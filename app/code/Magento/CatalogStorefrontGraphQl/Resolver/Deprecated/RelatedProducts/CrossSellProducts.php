<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Deprecated\RelatedProducts;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class CrossSellProducts extends \Magento\RelatedProductGraphQl\Model\Resolver\Batch\CrossSellProducts
{
    use RelatedProductsTrait;
}
