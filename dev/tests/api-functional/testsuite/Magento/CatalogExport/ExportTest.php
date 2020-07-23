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
     * @magentoApiDataFixture Magento/Catalog/_files/simple_product_with_all_attribute_types.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_date_attribute.php
     * @dataProvider attributesResult
     * @param [] $expectedAttributes
     */
    public function testAllAttributes($expectedAttributes)
    {
        $this->_markTestAsRestOnly('SOAP will be covered in another test');

        $this->reindex();

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple1');
        $simpleProductWithDate = $productRepository->get('simple_with_date');
        $this->createServiceInfo['rest']['resourcePath'] .= '?ids[0]=' . $product->getId() .
            '&ids[1]=' . $simpleProductWithDate->getId();

        $results = $this->_webApiCall($this->createServiceInfo, []);
        $attributesWithoutValueId = [];

        foreach ($results as $result) {
            if(isset($result['attributes'])) {
                $attributes = $result['attributes'];
                foreach ($attributes as $attribute) {
                    unset($attribute['value'][0]['id']); // unset id as it generates dynamically,
                    $attributesWithoutValueId[] = $attribute;
                }
            }
        }

        $this->assertEquals($expectedAttributes, $attributesWithoutValueId);
    }

    /**
     * Data Provider with eav attribute result
     *
     * @return array
     */
    public function attributesResult()
    {
        return [
            'attribute_results_export' => [
                [
                    [
                        'attribute_code' => 'boolean_attribute',
                        'type'  => 'boolean',
                        'value' => [
                            [
                                'value' => 'yes'
                            ]
                        ]
                    ],
                    [
                        'attribute_code' => 'image_attribute',
                        'type'  => 'media_image',
                        'value' => [
                            [
                                'value' => 'imagepath',
                            ]
                        ]
                    ],
                    [
                        'attribute_code' => 'multiselect_attribute',
                        'type'  => 'multiselect',
                        'value' => [
                            [
                                'value' => 'Option 1',
                            ]
                        ]
                    ],
                    [
                        'attribute_code' => 'decimal_attribute',
                        'type'  => 'price',
                        'value' => [
                            [
                                'value' => '100.000000',
                            ]
                        ]
                    ],
                    [
                        'attribute_code' => 'text_editor_attribute',
                        'type'  => 'textarea',
                        'value' => [
                            [
                                'value' => 'text Editor Attribute test',
                            ]
                        ]
                    ],
                    [
                        'attribute_code' => 'date_attribute',
                        'type'  => 'date',
                        'value' => [
                            [
                                'value' => date('Y-m-d 00:00:00'),
                            ]
                        ]
                    ],
                ],
            ]
        ];
    }
}
