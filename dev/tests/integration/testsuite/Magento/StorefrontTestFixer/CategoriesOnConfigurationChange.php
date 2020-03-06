<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

use Magento\CatalogInventoryExtractor\Plugin\UpdateCategoriesOnConfigurationChange;

/**
 * Mock for preferences in integration/etc/di/preferences/graphql.php to be able run integration tests
 */
class CategoriesOnConfigurationChange extends UpdateCategoriesOnConfigurationChange
{

}
