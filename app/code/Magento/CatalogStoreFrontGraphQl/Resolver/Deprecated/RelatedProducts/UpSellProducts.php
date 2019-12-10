<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver\Deprecated\RelatedProducts;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class UpSellProducts extends \Magento\RelatedProductGraphQl\Model\Resolver\Batch\UpSellProducts
{
    use RelatedProductsTrait;
}
