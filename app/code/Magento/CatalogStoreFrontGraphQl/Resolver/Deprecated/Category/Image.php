<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver\Deprecated\Category;

use Magento\CatalogStoreFrontGraphQl\Model\CategoryModelHydrator;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Override resolver of deprecated field. Add 'model' to output
 */
class Image extends \Magento\CatalogGraphQl\Model\Resolver\Category\Image
{
    /**
     * @var CategoryModelHydrator
     */
    private $categoryModelHydrator;

    /**
     * @param DirectoryList $directoryList
     * @param CategoryModelHydrator $categoryModelHydrator
     */
    public function __construct(
        DirectoryList $directoryList,
        CategoryModelHydrator $categoryModelHydrator
    ) {
        $this->categoryModelHydrator = $categoryModelHydrator;
        parent::__construct($directoryList);
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
