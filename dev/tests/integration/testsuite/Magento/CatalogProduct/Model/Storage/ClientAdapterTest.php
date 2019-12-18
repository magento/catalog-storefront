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
     * Get Simple Product Data
     *
     * @return array
     */
    protected function getSimpleProductData()
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
}
