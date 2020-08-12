<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Deprecated\Review;

use Magento\CatalogStorefrontGraphQl\Model\ProductModelHydrator;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\Review\Model\Review\SummaryFactory;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class RatingSummary extends \Magento\ReviewGraphQl\Model\Resolver\Product\RatingSummary
{
    /**
     * @var ProductModelHydrator
     */
    private $productModelHydrator;

    /**
     * @param SummaryFactory $summaryFactory
     * @param ReviewsConfig $reviewsConfig
     * @param ProductModelHydrator $productModelHydrator
     */
    public function __construct(
        SummaryFactory $summaryFactory,
        ReviewsConfig $reviewsConfig,
        ProductModelHydrator $productModelHydrator
    ) {
        parent::__construct($summaryFactory, $reviewsConfig);

        $this->productModelHydrator = $productModelHydrator;
    }

    /**
     * @inheritDoc
     *
     * Add 'model' to $value
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : float {
        $value = $this->productModelHydrator->hydrate($value);

        return parent::resolve($field, $context, $info, $value, $args);
    }
}
