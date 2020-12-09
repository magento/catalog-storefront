<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class GqlServerTest extends GraphQlAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGrpcRequest()
    {
        echo "URL" . TESTS_BASE_URL;
        $query = <<<QUERY
{
  productByID(id: 1) {
    id
    name
    }
}
QUERY;
        //        <const name="TESTS_BASE_URL" value="http://magento.url"/>
        $response = $this->graphQlQuery($query);

        print_r($response);
        self::assertEquals('1', $response['products']['items'][0]['id']);
    }
}
