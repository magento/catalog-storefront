<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontTestFixer;

/**
 * Plugin for collect product data during stock item update.
 *
 * Due to changes in DI (added afterSave() plugins) for store front application
 * we added empty plugin classes to keep plugin initialization chain
 */
class StorageOnStoreSaveFixer extends \Magento\CatalogStorefrontConnector\Model\Sync\SyncStorageOnStoreSave
{

}
