<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider;

/**
 * Provide data for requested attributes.
 */
class ColumnsDataMapper
{
    /**
     * Get list of attributes that can be founded among available attributes.
     *
     * Example:
     * ```
     * $requested = [
     *      'attribute_code'
     *      'label'
     *      'position'
     *      'values' => [....]
     * ]
     *
     * $available = [
     *      'attribute_code' => 'attribute.attribute_code',
     *      'attribute_id'   => 'attribute.attribute_id'
     *      'id'             => 'main_table.product_super_attribute_id',
     *      'label'          => new Zend_Db_Expr('')
     * ]
     *
     * $result = [
     *      'attribute_code' => 'attribute.attribute_code',
     *      'label'          => new Zend_Db_Expr('')
     * ]
     * ```
     *
     * @see \Magento\ConfigurableProductExtractor\DataProvider\Query\Attributes\ConfigurableOptionsBuilder::build
     * @see \Magento\ConfigurableProductExtractor\DataProvider\Query\Attributes\ConfigurableOptionValuesBuilder::build
     *
     * @param array $requested
     * @param array $available
     * @return array
     */
    public function filter(array $requested, array $available): array
    {
        if (empty($requested)) {
            $columns = $available;
        } else {
            $requestedAttributes = array_filter($requested, 'is_string');
            $columns = array_intersect_key($available, array_fill_keys($requestedAttributes, 1));
        }

        return $columns;
    }
}
