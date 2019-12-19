<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\Model\Storage;

use Magento\CatalogProduct\Model\Storage\Data\DocumentFactory;
use Magento\CatalogProduct\Model\Storage\Data\DocumentIteratorFactory;
use Magento\Integration\Api\AdminTokenServiceInterface;
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
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var DocumentIteratorFactory
     */
    private $documentIteratorFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->state = $this->objectManager->create(State::class);
        $this->storageClient = $this->objectManager->create(ElasticsearchClientAdapter::class);
        $this->adminTokens = Bootstrap::getObjectManager()->get(AdminTokenServiceInterface::class);
        $this->documentFactory = Bootstrap::getObjectManager()->get(DocumentFactory::class);
        $this->documentIteratorFactory = Bootstrap::getObjectManager()->get(DocumentIteratorFactory::class);

        $this->storageClient->createDataSource($this->state->getCurrentDataSourceName(), []);
        $this->storageClient->createEntity($this->state->getCurrentDataSourceName(), 'product', []);
        $this->storageClient->createAlias($this->state->getAliasName(), $this->state->getCurrentDataSourceName());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->storageClient->deleteDataSource($this->state->getCurrentDataSourceName());
    }

    /**
     * @return void
     */
    public function testBulkInsert(): void
    {
        $productBuilder = $this->getSimpleProductData();
        $productBuilder['sku'] = 'test-sku-default-site-123';
        $productData = $productBuilder;

        $this->storageClient->bulkInsert(
            $this->state->getAliasName(),
            'product',
            [$productData]
        );

        $entry = $this->storageClient->getEntry(
            $this->state->getAliasName(),
            'product',
            $productBuilder['id'],
            ['sku']
        );

        $this->assertEquals($productData['sku'], $entry->getData('sku'));

        $entry = $this->storageClient->getEntries(
            $this->state->getAliasName(),
            'product',
            [$productBuilder['id']],
            ['sku']
        )->current();

        $this->assertEquals($productData['sku'], $entry->getData('sku'));
    }

    /**
     * @return void
     */
    public function testSubquery(): void
    {
        $productBuilder = $this->getConfigurableProductData();
        $productBuilder['sku'] = 'test-configurable-product-with-variations-123';
        $productData = $productBuilder;

        $simple1 = $this->getSimpleProductData();
        $simple2 = $this->getSimpleProductData();
        $simple3 = $this->getSimpleProductData();
        $simple4 = $this->getSimpleProductData();

        $productData['parent_id'] = 'complex';
        $simple1['parent_id'] = [
            'name' => 'variant',
            'parent' => $productData['id']
        ];
        $simple2['parent_id'] = [
            'name' => 'variant',
            'parent' => $productData['id']
        ];
        $simple3['parent_id'] = [
            'name' => 'variant',
            'parent' => $productData['id']
        ];
        $simple4['parent_id'] = [
            'name' => 'variant',
            'parent' => $productData['id']
        ];

        $this->storageClient->bulkInsert(
            $this->state->getAliasName(),
            'product',
            [$productData, $simple1, $simple2, $simple3, $simple4]
        );

        $entry = $this->storageClient->getEntry(
            $this->state->getAliasName(),
            'product',
            $productBuilder['id'],
            ['sku', 'name', 'variants' => ['sku', 'name', 'price']]
        );

        $this->assertEquals($productData['sku'], $entry->getData('sku'));
        $this->assertEquals($simple1['id'], $entry->getVariants()->current()->getId());
        $entry->getVariants()->next();
        $this->assertEquals($simple2['price'], $entry->getVariants()->current()->getData('price'));
    }

    /**
     * @return void
     */
    public function testSubqueries(): void
    {
        $configurable1 = $this->getConfigurableProductData();
        $configurable1['sku'] = 'test-configurable-product-with-variations-123';

        $configurable2 = $this->getConfigurableProductData();
        $configurable2['sku'] = 'test-configurable-product-with-variations-123';

        $simple1 = $this->getSimpleProductData();
        $simple2 = $this->getSimpleProductData();

        $simple3 = $this->getSimpleProductData();
        $simple4 = $this->getSimpleProductData();
        $simple5 = $this->getSimpleProductData();

        $configurable1['parent_id'] = 'complex';
        $configurable2['parent_id'] = 'complex';
        $simple1['parent_id'] = [
            'name' => 'variant',
            'parent' => $configurable1['id']
        ];
        $simple2['parent_id'] = [
            'name' => 'variant',
            'parent' => $configurable2['id']
        ];
        $simple3['parent_id'] = [
            'name' => 'variant',
            'parent' => $configurable2['id']
        ];
        $simple4['parent_id'] = [
            'name' => 'variant',
            'parent' => $configurable1['id']
        ];
        $simple5['parent_id'] = [
            'name' => 'variant',
            'parent' => $configurable1['id']
        ];

        $this->storageClient->bulkInsert(
            $this->state->getAliasName(),
            'product',
            [$configurable1, $configurable2, $simple1, $simple2, $simple3, $simple4, $simple5]
        );

        $entries = $this->storageClient->getEntries(
            $this->state->getAliasName(),
            'product',
            [$configurable1['id'], $configurable2['id']],
            ['sku', 'name', 'variants' => ['sku', 'name', 'price']]
        );

        $this->assertEquals($configurable1['sku'], $entries->current()->getData('sku'));
        $this->assertEquals($simple1['id'], $entries->current()->getVariants()->current()->getId());
        $entries->next();
        $entries->current()->getVariants()->next();
        $this->assertEquals($simple3['price'], $entries->current()->getVariants()->current()->getData('price'));
    }

    /**
     * Get Simple Product Data
     *
     * @return array
     */
    private function getSimpleProductData()
    {
        return [
            'id' => rand(),
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
     * Get Configurable Product Data.
     *
     * @return array
     */
    private function getConfigurableProductData()
    {
        return [
            'id' => rand(),
            'sku' => uniqid('sku-', true),
            'name' => uniqid('name-', true),
            'visibility' => 4,
            'type_id' => 'configurable',
            'price' => 3.62,
            'status' => 1,
            'attribute_set_id' => 4,
            'custom_attributes' => [
                ['attribute_code' => 'cost', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Description'],
            ],
//            'variations' => [
//                '%id%' => ['product' => '%id%']
//            ]
        ];
    }
}
