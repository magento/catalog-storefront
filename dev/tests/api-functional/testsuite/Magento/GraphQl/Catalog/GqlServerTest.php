<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

/**
 * Test verify only work GraphQL Node server and Catalog Storefront service with gRPC API call
 */
class GqlServerTest extends \Magento\CatalogStorefront\Test\Api\StorefrontGraphQlTestsAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGrpcRequest()
    {
        $query = <<<QUERY
{
    getProductsByIds(ids: [1]) {
        items {
            sku
            name
            url_key
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        print_r($response);
        self::assertEquals('simple', $response['products']['items'][0]['sku']);
    }
}
