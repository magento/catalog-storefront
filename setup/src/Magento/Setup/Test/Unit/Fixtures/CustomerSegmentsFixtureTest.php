<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CustomerSegmentsFixture;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerSegmentsFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\CartPriceRulesFixture
     */
    private $model;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ruleFactoryMock;

    /**
     * @var \Magento\CustomerSegment\Model\SegmentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $segmentFactoryMock;

    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->createMock(\Magento\Setup\Fixtures\FixtureModel::class);
        $this->ruleFactoryMock = $this->createPartialMock(\Magento\SalesRule\Model\RuleFactory::class, ['create']);
        $this->segmentFactoryMock = $this->createPartialMock(
            \Magento\CustomerSegment\Model\SegmentFactory::class,
            ['create']
        );
        $this->model = new CustomerSegmentsFixture(
            $this->fixtureModelMock,
            $this->ruleFactoryMock,
            $this->segmentFactoryMock
        );
    }

    public function testExecute()
    {
        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $abstractDbMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [$contextMock],
            '',
            true,
            true,
            true,
            ['getAllChildren']
        );
        $abstractDbMock->expects($this->once())
            ->method('getAllChildren')
            ->willReturn([1]);

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->willReturn(2);

        $websiteMock = $this->createMock(\Magento\Store\Model\Website::class);
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->willReturn([$storeMock]);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn('website_id');

        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($abstractDbMock);
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/file');
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn('category_id');

        $ruleModelMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleModelMock);

        $segmentModelMock = $this->createMock(\Magento\CustomerSegment\Model\Segment::class);
        $this->segmentFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($segmentModelMock);

        $objectValueMap = [
            [\Magento\Catalog\Model\Category::class, $categoryMock]
        ];

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($storeManagerMock);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap($objectValueMap);

        $valueMap = [
            ['customer_segment_rules', 0, 1],
            ['cart_price_rules', 0, 1],
            ['customer_segments', 1, 1]
        ];

        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturnMap($valueMap);
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $ruleMock->expects($this->never())->method('save');

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with($this->equalTo(\Magento\SalesRule\Model\Rule::class))
            ->willReturn($ruleMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(0);

        $this->model->execute();
    }

    public function testGenerateCustomerSegments()
    {
        $segmentModelMock = $this->getMockBuilder(\Magento\CustomerSegment\Model\Segment::class)
            ->disableOriginalConstructor()
            ->addMethods(['getSegmentId', 'getApplyTo'])
            ->setMethods(['save', 'matchCustomers', 'loadPost'])
            ->getMock();

        $data1 = [
            'name'          => 'Customer Segment 0',
            'website_ids'   => [1],
            'is_active'     => '1',
            'apply_to'      => 0,
        ];
        $segmentModelMock->expects($this->at(0))
            ->method('loadPost')
            ->with($data1);
        $segmentModelMock->expects($this->once())
            ->method('getSegmentId')
            ->willReturn(1);
        $data2 = [
            'name'          => 'Customer Segment 0',
            'segment_id'    => 1,
            'website_ids'   => [1],
            'is_active'     => '1',
            'conditions'    => [
                1 => [
                    'type' => \Magento\CustomerSegment\Model\Segment\Condition\Combine\Root::class,
                    'aggregator' => 'any',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1' => [
                    'type' => \Magento\CustomerSegment\Model\Segment\Condition\Customer\Attributes::class,
                    'attribute' => 'email',
                    'operator' => '==',
                    'value' => 'user_1@example.com',
                ]
            ]
        ];
        $segmentModelMock->expects($this->at(3))
            ->method('loadPost')
            ->with($data2);
        $segmentModelMock->expects($this->exactly(2))
            ->method('save');
        $this->segmentFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($segmentModelMock);

        $valueMap = [
            ['customers', 0, 1]
        ];

        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturnMap($valueMap);

        $reflection = new \ReflectionClass($this->model);
        $reflectionProperty = $reflection->getProperty('customerSegmentsCount');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, 1);

        $this->model->generateCustomerSegments();
    }

    public function testGenerateSegmentCondition()
    {
        $firstCondition = [
            'type'      => \Magento\SalesRule\Model\Rule\Condition\Product::class,
            'attribute' => 'category_ids',
            'operator'  => '==',
            'value'     => 0,
        ];

        $secondCondition = [
            'type'      => \Magento\SalesRule\Model\Rule\Condition\Address::class,
            'attribute' => 'base_subtotal',
            'operator'  => '>=',
            'value'     => 0,
        ];

        $thirdCondition = [
            'type'      => \Magento\CustomerSegment\Model\Segment\Condition\Segment::class,
            'operator'  => '==',
            'value'     => 1,
        ];
        $expected = [
            'conditions' => [
                1 => [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1'=> [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
                '1--1--1' => $firstCondition,
                '1--2' => $secondCondition,
                '1--3' => $thirdCondition,
            ],
            'actions' => [
                1 => [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                ],
            ]
        ];

        $reflection = new \ReflectionClass($this->model);
        $reflectionProperty = $reflection->getProperty('customerSegmentsCount');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->model, 1);
        $result = $this->model->generateSegmentCondition(0, [[0]]);
        $this->assertSame($expected, $result);
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating customer segments and rules', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'customer_segment_rules' => 'Customer Segments and Rules'
        ], $this->model->introduceParamLabels());
    }
}
