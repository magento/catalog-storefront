<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Deprecated;

use Magento\CatalogStorefrontGraphQl\Model\ProductModelHydrator;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class Price extends \Magento\CatalogGraphQl\Model\Resolver\Product\Price
{
    /**
     * @var ProductModelHydrator
     */
    private $productModelHydrator;

    /**
     * @param PriceInfoFactory $priceInfoFactory
     * @param ProductModelHydrator $productModelHydrator
     */
    public function __construct(
        PriceInfoFactory $priceInfoFactory,
        ProductModelHydrator $productModelHydrator
    ) {
        $this->productModelHydrator = $productModelHydrator;
        parent::__construct($priceInfoFactory);
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
     * @throws \Exception
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
