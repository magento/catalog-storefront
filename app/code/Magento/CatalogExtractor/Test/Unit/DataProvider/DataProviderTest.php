<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\Test\Unit\DataProvider;

use Magento\CatalogExtractor\DataProvider\Transformer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for DataProvider
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \Magento\CatalogExtractor\DataProvider\DataProvider
     */
    private $dataProvider;

    /**
     * @var Transformer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transformerMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->transformerMock = $this->getMockBuilder(Transformer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformerMock->expects($this->any())->method('transform')->willReturnArgument(0);
        $this->dataProvider = (new ObjectManager($this))->getObject(
            \Magento\CatalogExtractor\DataProvider\DataProvider::class,
            [
                'defaultDataProvider' => DataProviderStub::class,
                'dataProviders' => [
                    'price' => DataProviderStub::class,
                    'items.options' => DataProviderStub::class,
                ],
                'objectManager' => $this->objectManagerMock,
                'transformer' => $this->transformerMock,
            ]
        );
    }

    /**
     * Test data provider
     *
     * @param array $attributes
     * @param array $expectedAttributes
     * @dataProvider getAttributesDataProvider
     */
    public function testDataProvider(array $attributes, array $expectedAttributes): void
    {
        $productId = 42;
        $expectedAttributes['type_id'] = 'simple';
        $expectedAttributes = [$productId => $expectedAttributes];
        $this->objectManagerMock->expects($this->atLeastOnce())->method('get')->willReturn(new DataProviderStub);
        $actual = $this->dataProvider->fetch([$productId], $attributes, []);
        $this->assertEquals($expectedAttributes, $actual);
    }

    /**
     * Test data provider with empty attributes
     */
    public function testEmptyAttributesDataProvider(): void
    {
        $productId1 = 42;
        $productId2 = 24;
        $expectedAttributes = [$productId1 => ['type_id' => 'simple'], $productId2 => ['type_id' => 'simple']];
        $this->objectManagerMock->expects($this->atLeastOnce())->method('get')->willReturn(new DataProviderStub);
        $actual = $this->dataProvider->fetch([$productId1, $productId2], [], []);
        $this->assertEquals($expectedAttributes, $actual);
    }

    /**
     * Get data set for test DataProvider
     *
     * @return array
     */
    public function getAttributesDataProvider(): array
    {
        return [
            'default attributes' => [
                ['default_attribute1', 'default_attribute2'],
                ['default_attribute1', 'default_attribute2'],
            ],
            'default with custom nested attributes' => [
                ['default_attribute1', 'price' => ['min_price', 'max_price']],
                ['default_attribute1', 'price' => ['min_price', 'max_price']],
            ],
            'default with custom attributes' => [
                ['default_attribute1', 'price' => []],
                ['default_attribute1', 'price' => []],
            ],
            'custom attributes' => [
                ['default_attribute1', 'items.options' => ['name', 'sku']],
                ['default_attribute1', 'options' => ['name', 'sku']],
            ],
            'custom nested attributes' => [
                ['price' => ['min_price']],
                ['price' => ['min_price']],
            ],
            'custom multi nested attributes' => [
                ['price' => ['min_price' => ['amount']]],
                ['price' => ['min_price' => ['amount']]],
            ],
            'default nested attribute do not include nested attributes' => [
                ['default_attribute1' => ['attribute1', 'attribute2']],
                ['default_attribute1'],
            ],
        ];
    }
}
