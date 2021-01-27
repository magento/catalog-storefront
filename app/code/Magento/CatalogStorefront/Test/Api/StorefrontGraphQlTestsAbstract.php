<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api;

/**
 * Test class: to be able run GQL query via GQL Node Server, which do request to Storefront Service (gRPC Server)
 */
abstract class StorefrontGraphQlTestsAbstract extends \Magento\TestFramework\TestCase\GraphQlAbstract
{
    use Storefront;
}
