<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StorefrontGraphQl\Model;

use Magento\Framework\Cache\FrontendInterface;

/**
 * Resolve interface arguments and collect them in to the Reflection cache
 */
class ArgumentResolver
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get name of interface arrays passed as parameter to API class
     *
     * @param string $queryClassName
     * @param string $serviceMethodName
     * @return string
     * @throws \ReflectionException
     * @throws \LogicException
     */
    public function getArgumentClassName(string $queryClassName, string $serviceMethodName): string
    {
        $parameterClassName = $this->getFromCache($queryClassName, $serviceMethodName);
        if (empty($parameterClassName)) {
            $parameterClassName = $this->getParameterClassName($queryClassName, $serviceMethodName);
            $this->addToCache($queryClassName, $serviceMethodName, $parameterClassName);
        }

        return $parameterClassName;
    }

    /**
     * Add class arguments to cache
     *
     * @param string $class
     * @param string $method
     * @param string $className
     * @return void
     */
    private function addToCache(string $class, string $method, string $className): void
    {
        $this->cache->save(
            $className,
            $this->getCacheKey($class, $method)
        );
    }

    /**
     * Get class arguments from cache
     *
     * @param string $class
     * @param string $method
     * @return string
     */
    private function getFromCache(string $class, string $method): string
    {
        return $this->cache->load($this->getCacheKey($class, $method)) ?: '';
    }

    /**
     * Retrieve cache key
     *
     * @param string $class
     * @param string $method
     * @return string
     */
    private function getCacheKey(string $class, string $method): string
    {
        return 'arguments_' . $class . '_' . $method;
    }

    /**
     * Get method doc block and retrieve class interface from it
     * Method parameter should be specified in method DockBlock as array of classes
     * with fully specified class name: "\Magento\FullClassNamespace\ClassName"
     *
     * @param string $className
     * @param string $methodName
     * @return string
     * @throws \ReflectionException
     * @throws \LogicException
     * @throws \RuntimeException
     */
    private function getParameterClassName(string $className, string $methodName): string
    {
        $class = new \ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $params = $method->getParameters();
        try {
            $exceptionMessage = 'Class passed to resolver is not compatible with ArgumentResolver';
            if (count($params) !== 1) {
                throw new \RuntimeException($exceptionMessage);
            }
            $param = current($params);
            $class = $param->getClass();
            $className = $class->name;
        } catch (\Exception $exception) {
            throw new \RuntimeException($exceptionMessage);
        }

        return $className;
    }
}
