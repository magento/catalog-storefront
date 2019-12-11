<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\MessageBus;

use PHPUnit\Framework\TestCase;

/**
 * Class for category url rewrites tests
 *
 * @magentoDbIsolation enabled
 */
class ConsumerTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_position.php
     * @dataProvider categoryProvider
     * @param array $data
     * @return void
     */
    public function testProcess(array $data): void
    {

    }

    protected function getProduct($sku, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['sku' => $sku], null, $storeCode);

        return $response;
    }

    /**
     * @return array
     */
    public function processProvider(): array
    {
        return [
            'variation 1' => [
                [
                    'data' => [

                    ],
                    'expected_data' => [

                    ],
                ],
            ],
        ];
    }
}
