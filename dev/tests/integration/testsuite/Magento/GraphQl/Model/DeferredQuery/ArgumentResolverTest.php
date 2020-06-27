<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\DeferredQuery;

use Magento\Framework\App\Cache\Type\Reflection as ReflectionCache;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\StorefrontGraphQl\Model\ArgumentResolver;

/**
 * Tests for the \Magento\GraphQl\Model\DeferredQuery\ArgumentResolver class
 */
class ArgumentResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->argumentResolver = $this->objectManager->get(ArgumentResolver::class);
    }

    /**
     * Test for getArgumentClass()
     */
    public function testGetArgumentClassName()
    {
        $queryClassName = \Magento\GraphQl\Model\DeferredQuery\TestClass\ArgumentResolverClassInterface::class;
        $serviceMethodName = 'methodName';
        $this->assertEquals(
            'Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface',
            $this->argumentResolver->getArgumentClassName($queryClassName, $serviceMethodName)
        );
    }

    /**
     * Test for getArgumentClass() with cached arguments
     *
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @throws \ReflectionException
     */
    public function testGetCachedArgumentClassName()
    {
        /** @var  \Magento\Framework\App\Cache\StateInterface $cacheState */
        $cacheState = $this->objectManager->get(\Magento\Framework\App\Cache\StateInterface::class);
        $cacheState->setEnabled(ReflectionCache::TYPE_IDENTIFIER, true);

        $queryClassName = \Magento\GraphQl\Model\DeferredQuery\TestClass\ArgumentResolverClassInterface::class;
        $serviceMethodName = 'methodName';
        $this->argumentResolver->getArgumentClassName($queryClassName, $serviceMethodName);
        /** @var ReflectionCache $cache */
        $cache = $this->objectManager->get(ReflectionCache::class);
        $cacheKey = 'arguments_' . $queryClassName . '_' . $serviceMethodName;
        $cache = $cache->load($cacheKey);

        $this->assertEquals('Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface', $cache);
    }

    /**
     * Test for getArgumentClass() without params in method
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetArgumentClassNameWithoutParams(): void
    {
        $this->expectException('\RuntimeException');
        $this->expectExceptionMessage('Class passed to resolver is not compatible with ArgumentResolver');

        $queryClassName = \Magento\GraphQl\Model\DeferredQuery\TestClass\ArgumentResolverClassInterface::class;
        $serviceMethodName = 'methodNameWithoutParameters';
        $this->argumentResolver->getArgumentClassName($queryClassName, $serviceMethodName);
    }

    /**
     * Test for getArgumentClass() with not existing class
     *
     * @return void
     */
    public function testGetArgumentClassNameWithNotExistingClass(): void
    {
        $this->expectException('\ReflectionException');
        $this->expectExceptionMessage('Class \Magento\NotExistingNamespace\NotExistingClass does not exist');

        $queryClassName = '\Magento\NotExistingNamespace\NotExistingClass';
        $serviceMethodName = 'create';
        $this->argumentResolver->getArgumentClassName($queryClassName, $serviceMethodName);
    }

    /**
     * Test for getArgumentClass() with not existing method
     *
     * @return void
     */
    public function testGetArgumentClassNameWithNotExistingMethod(): void
    {
        $this->expectException('\ReflectionException');
        $this->expectExceptionMessage('Method notExistingMethodName does not exist');

        $queryClassName = \Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface::class;
        $serviceMethodName = 'notExistingMethodName';
        $this->argumentResolver->getArgumentClassName($queryClassName, $serviceMethodName);
    }

    /**
     * Test for getArgumentClass() with not correct class
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetArgumentClassNameWithNotCorrectClass()
    {
        $this->expectException('\RuntimeException');
        $this->expectExceptionMessage('Class passed to resolver is not compatible');

        $queryClassName = \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface::class;
        $serviceMethodName = 'create';
        $this->argumentResolver->getArgumentClassName($queryClassName, $serviceMethodName);
    }

    /**
     * Test for getArgumentClass() with more than one parameter
     *
     * @return void
     */
    public function testGetArgumentClassNameWithMoreThanOneParametersMethod(): void
    {
        $this->expectException('\RuntimeException');
        $this->expectExceptionMessage('Class passed to resolver is not compatible');

        $queryClassName = \Magento\GraphQl\Model\DeferredQuery\TestClass\ArgumentResolverClassInterface::class;
        $serviceMethodName = 'methodNameMoreOneParameters';
        $this->argumentResolver->getArgumentClassName($queryClassName, $serviceMethodName);
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        /** @var  \Magento\Framework\App\Cache\StateInterface $cacheState */
        $cacheState = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\Cache\StateInterface::class);
        $cacheState->setEnabled(ReflectionCache::TYPE_IDENTIFIER, false);
    }
}
