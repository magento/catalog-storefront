<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver\Deprecated\Category;

use Magento\CatalogStoreFrontGraphQl\Model\CategoryModelHydrator;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Block as BlockProvider;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class Block extends \Magento\CatalogCmsGraphQl\Model\Resolver\Category\Block
{
    /**
     * @var CategoryModelHydrator
     */
    private $categoryModelHydrator;

    /**
     * @param BlockProvider $blockProvider
     * @param CategoryModelHydrator $categoryModelHydrator
     */
    public function __construct(
        BlockProvider $blockProvider,
        CategoryModelHydrator $categoryModelHydrator
    ) {
        $this->categoryModelHydrator = $categoryModelHydrator;
        parent::__construct($blockProvider);
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
        $value = $this->categoryModelHydrator->hydrate($value);
        return parent::resolve($field, $context, $info, $value, $args);
    }
}
