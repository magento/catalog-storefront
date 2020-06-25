<?php
/**
 * Class for the ArgumentResolver test
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\DeferredQuery\TestClass;

use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResultInterface;

/**
 * Class  ArgumentResolverClassInterface
 */
interface ArgumentResolverClassInterface
{
    /**
     * Method with correct params
     *
     * @param ProductsGetRequestInterface $request
     * @return ProductsGetResultInterface
     */
    public function methodName(ProductsGetRequestInterface $request): ProductsGetResultInterface;

    /**
     * Method without parameters
     *
     * @return void
     */
    public function methodNameWithoutParameters();

    /**
     * Method with more that one parameter
     *
     * @param ProductsGetRequestInterface $requestFirst
     * @param ProductsGetRequestInterface $requestSecond
     * @return void
     */
    public function methodNameMoreOneParameters(
        ProductsGetRequestInterface $requestFirst,
        ProductsGetRequestInterface $requestSecond
    );
}
