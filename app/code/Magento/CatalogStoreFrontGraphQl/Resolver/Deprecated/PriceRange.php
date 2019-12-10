<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver\Deprecated;

use Magento\CatalogStoreFrontGraphQl\Model\ProductModelHydrator;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class PriceRange extends \Magento\CatalogGraphQl\Model\Resolver\Product\PriceRange
{
    /**
     * @var ProductModelHydrator
     */
    private $productModelHydrator;

    /**
     * @param PriceProviderPool $priceProviderPool
     * @param Discount $discount
     * @param ProductModelHydrator $productModelHydrator
     */
    public function __construct(
        PriceProviderPool $priceProviderPool,
        Discount $discount,
        ProductModelHydrator $productModelHydrator
    ) {
        $this->productModelHydrator = $productModelHydrator;
        parent::__construct($priceProviderPool, $discount);
    }

    /**
     * @inheritdoc
     *
     * Add 'model' to $value
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $value = $this->productModelHydrator->hydrate($value);
        return parent::resolve($field, $context, $info, $value, $args);
    }
}
