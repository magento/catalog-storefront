<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogDataExporter\Model\Feed\Products;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Indexer\Model\Indexer;

/**
 * Class AbstractProductExportTestHelper
 *
 * @magentoAppIsolation enabled
 */
abstract class AbstractProductExportTestHelper extends WebapiAbstract
{
    /**
     * @var array
     */
    protected $createServiceInfo;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Products
     */
    protected $productsFeed;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

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
        'categories',
        'in_stock',
        'low_stock',
        'url',
    ];

    /**
     * @var array
     */
    private $optionsToCompare = [];

    /**
     * @var array
     */
    private $optionValuesToCompare = [];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productsFeed = $this->objectManager->get(Products::class);
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

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
     * Validate product data
     *
     * @param array $expected
     * @param array $actual
     * @return void
     */
    protected function assertProductsEquals(array $expected, array $actual): void
    {
        $n = sizeof($expected);
        for ($i = 0; $i < $n; $i++) {
            foreach ($this->attributesToCompare as $attribute) {
                $this->compareComplexValue(
                    $expected[$i][$this->snakeToCamelCase($attribute)],
                    $actual[$i][$attribute]
                );
            }
            $this->assertOptionsEquals(
                $expected[$i]['options'],
                $actual[$i]['options']
            );
        }
    }

    /**
     * Validate product options in extracted product data
     *
     * @param array $expectedOptions
     * @param array $actualOptions
     * @return void
     */
    protected function assertOptionsEquals(array $expectedOptions, array $actualOptions): void
    {
        $this->assertCount(sizeof($expectedOptions), $actualOptions);
        foreach ($actualOptions as $optionKey => $actualOption) {
            foreach ($this->optionsToCompare as $optionToCompare) {
                $this->assertEquals(
                    $expectedOptions[$optionKey][$optionToCompare],
                    $actualOption[$optionToCompare]
                );
            }

            $this->assertCount(sizeof($expectedOptions[$optionKey]['values']), $actualOption['values']);
            foreach ($actualOption['values'] as $valueKey => $value) {
                foreach ($this->optionValuesToCompare as $optionValueToCompare) {
                    $this->assertEquals(
                        $expectedOptions[$optionKey]['values'][$valueKey][$optionValueToCompare],
                        $value[$optionValueToCompare]
                    );
                }
            }
        }
    }

    /**
     * Compares complex values
     *
     * @param mixed $expected
     * @param mixed $actual
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
     * @return string string
     */
    private function camelToSnakeCase($string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * Run the indexer to extract product data
     *
     * @return void
     */
    protected function runIndexer(): void
    {
        try {
            $this->indexer->load('catalog_data_exporter_products');
            $this->indexer->reindexAll();
        } catch (\Throwable $e) {
            $this->fail("Couldn`t run catalog_data_exporter_products reindex" . $e->getMessage());
        }
    }
}
