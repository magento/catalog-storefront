<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataForUpdate;

/**
 * Mock for preferences in integration/etc/di/preferences/graphql.php to be able run integration tests
 *
 * Due to changes in DI (added afterSave() plugins) for store front application
 * we added empty plugin classes to keep plugin initialization chain
 */
class CategoryOnUpdate extends CollectCategoriesDataForUpdate
{

}
