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
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\CatalogGraphQl\Model\Resolver\Product\Websites\Collection;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class Websites extends \Magento\CatalogGraphQl\Model\Resolver\Product\Websites
{
    /**
     * @var ProductModelHydrator
     */
    private $productModelHydrator;

    /**
     * @param ValueFactory $valueFactory
     * @param Collection $productWebsitesCollection
     * @param ProductModelHydrator $productModelHydrator
     */
    public function __construct(
        ValueFactory $valueFactory,
        Collection $productWebsitesCollection,
        ProductModelHydrator $productModelHydrator
    ) {
        $this->productModelHydrator = $productModelHydrator;
        parent::__construct($valueFactory, $productWebsitesCollection);
    }

    /**
     * @inheritdoc
     *
     * Add 'model' to $value
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
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
        if (!empty($field->getDeprecated())) {
            $value = $this->productModelHydrator->hydrate($value, $field);
        }
        return parent::resolve($field, $context, $info, $value, $args);
    }
}
