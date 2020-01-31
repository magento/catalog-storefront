<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\Category;

/**
 * Plugin for collect category data during saving process
 *
 * Due to changes in DI (added afterSave() plugins) for store front application
 * we added empty plugin classes to keep plugin initialization chain
 */
class CategoryAfterSave extends \Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataForUpdate
{

}
