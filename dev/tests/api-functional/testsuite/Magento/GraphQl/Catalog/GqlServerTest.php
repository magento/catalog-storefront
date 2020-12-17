<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;


class GqlServerTest extends \Magento\CatalogStorefront\Test\Api\StorefrontGraphQlTestsAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     *
     * 1. execute Magento/Catalog/_files/product_simple.php
     * 1.1 create product in Magent
     * 2. trigger indexer "on save" for CatalogFeedIndexer
     * 3. indexser triggers callback \Magento\CatalogExport\Model\Indexer\EntityIndexerCallback::execute
     * 3. callback send into Message Bus message with product updae {id:x} RabiitMQ
     * 4. Consumer in Message Broker accept message: \Magento\CatalogMessageBroker\Model\MessageBus\Product\ProductsConsumer::processMessage
     * 4.1 Consumer send REST to Magento go get product data (collected recently by feed indexer)
     * 4.2. Consumer send data to Catalog Service via gRPC
     *
     * call Query "productByID"
     * 1. send request to "TESTS_BASE_URL" (overriden in phpunit.xml for GQL Server)
     * 2. GQL Server accept query and
     *   - go to Storefront Service to get Data \Magento\CatalogStorefront\Model\CatalogService::getProducts
     */
    public function testGrpcRequest()
    {
        $query = <<<QUERY
{
  productByID(id: 1) {
    id
    name
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        print_r($response);
        self::assertEquals('simple', $response['productByID']['id']);
    }
}
