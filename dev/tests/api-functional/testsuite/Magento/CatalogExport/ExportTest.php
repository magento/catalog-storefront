<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @magentoAppIsolation enabled
 */
class ExportTest extends WebapiAbstract
{
    /**
     * @var array
     */
    private $createServiceInfo;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\CatalogDataExporter\Model\Feed\Products
     */
    private $productsFeed;

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'sku',
        'name',
        'type',
        //'meta_description',
        //'meta_keyword',
        //'meta_title',
        'status',
        'tax_class_id',
        'created_at',
        'updated_at',
        'url_key',
        'visibility',
        //'weight',
        'currency',
        'displayable',
        'buyable',
        'attributes',
        'categories',
        'options',
        'in_stock',
        'low_stock',
        'url',
    ];

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productsFeed = $this->objectManager->get(\Magento\CatalogDataExporter\Model\Feed\Products::class);

        $this->createServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/catalog-export/products',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogExportApiProductRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogExportApiProductRepositoryV1Get',
            ],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute.php
     */
    public function testExport()
    {
        $this->_markTestAsRestOnly('SOAP will be covered in another test');

        $this->reindex();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        $this->createServiceInfo['rest']['resourcePath'] .= '?ids[0]=' . $product->getId();
        $result = $this->_webApiCall($this->createServiceInfo, []);
        $this->assertProductsEquals($this->productsFeed->getFeedByIds([$product->getId()])['feed'], $result);
    }

    private function assertProductsEquals(array $expected, array $actual)
    {
        $n = sizeof($expected);
        for ($i = 0; $i < $n; $i++) {
            foreach ($this->attributesToCompare as $attribute) {
                $this->compareComplexValue(
                    $expected[$i][$this->snakeToCamelCase($attribute)],
                    $actual[$i][$attribute]
                );
            }
        }
    }

    private function compareComplexValue($expected, $actual)
    {
        if (is_array($expected)) {
            $this->assertEquals(
                sizeof($expected),
                sizeof($actual),
                'Expected and actual are of different size, expected '
                . json_encode($expected)
                . ', actual '
                . json_encode($actual)
                . '.'
            );
            foreach (array_keys($expected) as $key) {
                $snakeCaseKey = $this->camelToSnakeCase($key);
                $this->assertTrue(
                    isset($actual[$snakeCaseKey]),
                    $snakeCaseKey . 'doesn\'t exist, '
                    . json_encode($expected)
                    . ', actual '
                    . json_encode($actual)
                    . '.'
                );
                $this->compareComplexValue($expected[$key], $actual[$snakeCaseKey]);
            }
        } else {
            $this->assertEquals($expected, $actual);
        }
    }

    private function snakeToCamelCase($string)
    {
        $string = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        $string[0] = strtolower($string[0]);
        return $string;
    }

    private function camelToSnakeCase($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function reindex()
    {
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento indexer:reindex");
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/simple_products_eav.php
     */
    public function testEavAttributes()
    {

        $this->_markTestAsRestOnly('SOAP will be covered in another test');

        $this->reindex();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple1');

        $this->createServiceInfo['rest']['resourcePath'] .= '?ids[0]=' . $product->getId();
        $result = $this->_webApiCall($this->createServiceInfo, []);

        //todo:: weee attribute is not coming through web api
        //todo:: date attribute is not coming through web api
        //todo:: datetime attribute is not coming through web api

        $eavAttributes = [
            'text' => [
                'attribute_code' => 'text_attribute',
                'value' => 'text Attribute test'

            ],
            'multiselect' => [
                'attribute_code' => 'multiselect_attribute',
                'value' => 'Option 1'
            ],
            'textarea' => [
                'attribute_code' => 'text_area_attribute',
                'value' => 'text Area Attribute test'
            ],
            'texteditor' => [
                'attribute_code' => 'text_editor_attribute',
                'value' => 'text Editor Attribute test'

            ],
            'datetime' => [
                'attribute_code' => 'datetime_attribute',
                'value' => date('Y-m-d H:i:s')

            ],
            'date' => [
                'attribute_code' => 'date_attribute',
                'value' => date('Y-m-d')

            ],
            'boolean' => [
                'attribute_code' => 'boolean_attribute',
                'value' => 'yes'
            ],

            'price' => [
                'attribute_code' => 'price_attribute',
                'value' => '100.000000'
            ],
            'media_image' => [
                'attribute_code' => 'image_attribute',
                'value' => 'imagepath'

            ],
            'weee' => [
                'attribute_code' => 'weee_attribute',
                'value' => 10

            ],
        ];

        $attributes = $result[0]['attributes'];

        foreach ($attributes as $attribute) {
            foreach ($eavAttributes as $eavAttribute) {
                if(in_array($eavAttribute['attribute_code'],$attribute)) {
                    $this->assertTrue($eavAttribute['value'] === $attribute['value'][0]['value']);
                }

            }
        }
    }

    /**
     * @magentoApiDataFixture Magento/Swatches/_files/configurable_product_two_attributes.php
     */
    public function testEavSwatchAttributes()
    {
        $this->_markTestAsRestOnly('SOAP will be covered in another test');

        $this->reindex();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('configurable');

        $this->createServiceInfo['rest']['resourcePath'] .= '?ids[0]=' . $product->getId();
        $result = $this->_webApiCall($this->createServiceInfo, []);

        $options = \Safe\json_decode($result[0]['options'][0])->values;
        $arrayOptions = [
            'Option 1',
            'Option 2',
            'Option 3'
        ];

        foreach ($options as $option) {
            $this->assertContains($option->value, $arrayOptions);
        }
    }
}
