<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage;

use Magento\CatalogProduct\Model\Storage\ElasticsearchClientAdapter;
use Magento\CatalogProduct\Model\Storage\State;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\TestCase\WebApiHelper;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use \Magento\Framework\ObjectManagerInterface;

class ClientAdapterTest extends TestCase
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
     * @var ElasticsearchClientAdapter
     */
    private $storageClient;

    /**
     * @var State
     */
    private $state;

    /**
     * @var AdminTokenServiceInterface
     */
    private $adminTokens;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
//        $this->webApiHelper = $this->objectManager->create(WebApiHelper::class);
        $this->state = $this->objectManager->create(State::class);
        $this->storageClient = $this->objectManager->create(ElasticsearchClientAdapter::class);
        $this->adminTokens = Bootstrap::getObjectManager()->get(AdminTokenServiceInterface::class);

        $this->storageClient->deleteDataSource($this->state->getCurrentDataSourceName());
        $this->storageClient->createDataSource($this->state->getCurrentDataSourceName(), []);
        $this->storageClient->createEntity($this->state->getCurrentDataSourceName(), 'product', []);
        $this->storageClient->createAlias($this->state->getAliasName(), $this->state->getCurrentDataSourceName());
    }

    /**
     * @return void
     */
    public function testBulkInsert(): void
    {
        $productBuilder = $this->getSimpleProductData();
        $productBuilder['sku'] = 'test-sku-default-site-123';
//        $id = $this->saveProduct($productBuilder);
//        $productData = $this->getProduct('test-sku-default-site-123');
        $productData = $productBuilder;

        $this->storageClient->bulkInsert($this->state->getAliasName(), 'product', $productData);

        $entry = $this->storageClient->getEntry(
            $this->state->getAliasName(),
            'product',
            $productBuilder['id'],
            ['sku']
        );

        $t= 9;

    }

    /**
     * Get Simple Product Data
     *
     * @return array
     */
    protected function getSimpleProductData()
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

        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    protected function getProduct($sku, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
//            'soap' => [
//                'service' => self::SERVICE_NAME,
//                'serviceVersion' => self::SERVICE_VERSION,
//                'operation' => self::SERVICE_NAME . 'Get',
//            ],
        ];
        $response = $this->webApiHelper->_webApiCall($serviceInfo, ['sku' => $sku], null, $storeCode);

        return $response;
    }
}
