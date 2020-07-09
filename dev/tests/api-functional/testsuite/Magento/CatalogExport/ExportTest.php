<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport;

use Magento\CatalogDataExporter\Model\Feed\Products;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Indexer\Model\Indexer;

/**
 * Class ExportTest
 * @magentoAppIsolation enabled
 */
class ExportTest extends WebapiAbstract
{
    /**
     * @var array
     */
    private $createServiceInfo;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Products
     */
    private $productsFeed;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'sku',
        'name',
        'type',
        'status',
        'tax_class_id',
        'created_at',
        'updated_at',
        'url_key',
        'visibility',
        'currency',
        'displayable',
        'buyable',
        'attributes',
        'categories', //?
        'options',
        'in_stock',
        'low_stock',
        'url',
    ];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productsFeed = $this->objectManager->get(Products::class);
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);

        $this->createServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/catalog-export/products',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogExportApiProductRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogExportApiProductRepositoryV1Get',
            ],
        ];
    }

    /**
     * Test product export REST API
     *
     * @magentoApiDataFixture Magento/CatalogRule/_files/configurable_product.php
     *
     * @return void
     * @throws \Throwable
     */
    public function testExport()
    {
        $this->_markTestAsRestOnly('SOAP will be covered in another test');

        $this->runIndexer();

        $simpleId = '1';
        $configurableId = '666';

        $this->createServiceInfo['rest']['resourcePath'] .= '?ids[0]=' . $simpleId . '&ids[1]=' . $configurableId;
        $result = $this->_webApiCall($this->createServiceInfo, []);
        $feedData = $this->productsFeed->getFeedByIds([$simpleId, $configurableId])['feed'];

        foreach($feedData as $key => $productFeed) {
            $feedData[$key]['options'] = $productFeed['options'] ? $this->unsetEmptyValues($productFeed['options']) : null;
        }

        $this->assertProductsEquals($feedData, $result);
    }

    /**
     * @param array $expected
     * @param array $actual
     *
     * @return void
     */
    private function assertProductsEquals(array $expected, array $actual): void
    {
        $n = sizeof($expected);
        for ($i = 0; $i < $n; $i++) {
            foreach ($this->attributesToCompare as $attribute) {
                $this->compareComplexValue(
                    $expected[$i][$this->snakeToCamelCase($attribute)],
                    $actual[$i][$attribute],
                );
            }
        }
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     *
     * @return void
     */
    private function compareComplexValue($expected, $actual): void
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

    /**
     * Tranform snake case to camel case
     *
     * @param string|string[] $string
     * @return string|string[]
     */
    private function snakeToCamelCase($string)
    {
        $string = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        $string[0] = strtolower($string[0]);
        return $string;
    }

    /**
     * Tranform camel case to snake case
     *
     * @param string|int $string
     *
     * @return string string
     */
    private function camelToSnakeCase($string) : string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * Run the indexer to extract product data
     *
     * @return void
     * @throws \Throwable
     */
    protected function runIndexer() : void
    {
        $this->indexer->load('catalog_data_exporter_products');
        $this->indexer->reindexAll();
    }

    /**
     * Remove empty values from nested array recursively
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function unsetEmptyValues($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->unsetEmptyValues($value);
                }
                if ($value === null) {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }
}
