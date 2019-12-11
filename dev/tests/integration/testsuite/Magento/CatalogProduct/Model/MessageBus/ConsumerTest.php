<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\MessageBus;

use Magento\CatalogProduct\Model\Storage\ElasticsearchClientAdapter;
use Magento\CatalogProduct\Model\Storage\State;
use Magento\Store\Model\WebsiteRepository;
use Magento\TestFramework\TestCase\WebApiHelper;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use \Magento\Framework\ObjectManagerInterface;


/**
 * Class for category url rewrites tests
 *
 * @magentoDbIsolation enabled
 */
class ConsumerTest extends TestCase
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @var WebApiHelper
     */
    private $webApiHelper;

    /**
     * @var Consumer
     */
    private $object;

    /**
     * @var ElasticsearchClientAdapter
     */
    private $storageClient;

    /**
     * @var State
     */
    private $state;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->webApiHelper = $this->objectManager->create(WebApiHelper::class);
        $this->object = $this->objectManager->create(Consumer::class);
        $this->state = $this->objectManager->create(State::class);
        $this->storageClient = $this->objectManager->create(ElasticsearchClientAdapter::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_position.php
     * @dataProvider processProvider
     * @param array $data
     * @return void
     */
    public function testProcess(array $data): void
    {
        $productBuilder = $this->getSimpleProductData();
        $productBuilder['sku'] = 'test-sku-default-site-123';
        $websitesData = [
            'website_ids' => [
                1,
            ]
        ];
        $productBuilder['extension_attributes'] = $websitesData;
        $this->saveProduct($productBuilder);

        $this->object->process();

        $entry = $this->storageClient->getEntry(
            $this->state->getAliasName(),
            'product',
            $productBuilder['id'],
            ['sku']
        );
    }

    /**
     * Get Simple Product Data
     *
     * @param array $productData
     * @return array
     */
    protected function getSimpleProductData($productData = [])
    {
        return [
            'id' => uniqid(),
            'sku' => uniqid('sku-', true),
            'name' => uniqid('name-', true),
            'visibility' => 4,
            'type_id' => 'simple',
            'price' => 3.62,
            'status' => 1,
            'attribute_set_id' => 4,
            'custom_attributes' => [
                ['attribute_code' => 'cost', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Description'],
            ]
        ];
    }


    /**
     * Save Product
     *
     * @param $product
     * @param string|null $storeCode
     * @param string|null $token
     * @return mixed
     */
    protected function saveProduct($product, $storeCode = null, ?string $token = null)
    {
        if (isset($product['custom_attributes'])) {
            foreach ($product['custom_attributes'] as &$attribute) {
                if ($attribute['attribute_code'] == 'category_ids'
                    && !is_array($attribute['value'])
                ) {
                    $attribute['value'] = [""];
                }
            }
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if ($token) {
            $serviceInfo['rest']['token'] = $serviceInfo['soap']['token'] = $token;
        }
        $requestData = ['product' => $product];

        return $this->webApiHelper->_webApiCall($serviceInfo, $requestData, null, $storeCode);
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
        $response = $this->webApiHelper->_webApiCall($serviceInfo, ['sku' => $sku], null, $storeCode);

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
