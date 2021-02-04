<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Environment initialization
 */
error_reporting(E_ALL);
if (in_array('phar', \stream_get_wrappers())) {
    stream_wrapper_unregister('phar');
}
#ini_set('display_errors', 1);

require_once __DIR__ . '/autoload.php';
// Sets default autoload mappings, may be overridden in Bootstrap::create
\Magento\Framework\App\Bootstrap::populateAutoloader(BP, []);
