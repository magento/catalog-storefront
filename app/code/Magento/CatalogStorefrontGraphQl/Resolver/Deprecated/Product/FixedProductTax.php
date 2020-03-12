<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver\Deprecated\Product;

use Magento\CatalogStoreFrontGraphQl\Model\ProductModelHydrator;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Weee\Helper\Data;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class FixedProductTax extends \Magento\WeeeGraphQl\Model\Resolver\FixedProductTax
{
    /**
     * @var ProductModelHydrator
     */
    private $productModelHydrator;

    /**
     * @param ProductModelHydrator $productModelHydrator
     * @param Data $weeeHelper
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        ProductModelHydrator $productModelHydrator,
        Data $weeeHelper,
        TaxHelper $taxHelper
    ) {
        parent::__construct($weeeHelper, $taxHelper);
        $this->productModelHydrator = $productModelHydrator;
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
