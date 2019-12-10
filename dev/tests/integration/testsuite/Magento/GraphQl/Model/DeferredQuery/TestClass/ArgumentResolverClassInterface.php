<?php
/**
 * Class for the ArgumentResolver test
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\DeferredQuery\TestClass;

/**
 * Class  ArgumentResolverClassInterface
 */
interface ArgumentResolverClassInterface
{
    /**
     * Method with correct params
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[] $entry
     * @return void
     */
    public function methodName(
        array $entry
    );

    /**
     * Method without parameters
     *
     * @return void
     */
    public function methodNameWithoutParameters();

    /**
     * Method with more that one parameter
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[] $entry
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[] $anotherEntry
     * @return void
     */
    public function methodNameMoreOneParameters(
        array $entry,
        array $anotherEntry
    );
}
