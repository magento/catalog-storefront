<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class NewMediaGalleryTest extends GraphQlAbstract
{
    /**
     * Test which checks that image placeholder returns null
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testImagePlaceholderReturnNull()
    {
        $productSku = 'simple';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
        small_image {
            url
        }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertNull($response['products']['items'][0]['small_image']);
    }
}
