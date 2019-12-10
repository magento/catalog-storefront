<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStoreFrontGraphQl\Resolver\Product;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogProductApi\Api\Data\ProductResultContainerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;

/**
 * Format response from Storefront service
 */
class OutputFormatter
{
    /**
     * @param ProductResultContainerInterface $result
     * @param GraphQlInputException $e
     * @param BatchRequestItemInterface $request
     * @return array
     * @throws GraphQlInputException
     */
    public function __invoke(
        ProductResultContainerInterface $result,
        GraphQlInputException $e,
        BatchRequestItemInterface $request
    ) {
        $errors = $result->getErrors();
        if (!empty($errors)) {
            //ad-hoc solution with __() as GraphQlInputException accepts Phrase in construct
            //TODO: change with error holder
            throw new GraphQlInputException(__(\implode('; ', \array_map('\strval', $errors))));
        }

        $metaInfo = $result->getMetaInfo();
        return [
            'items' => $result->getItems(),
            'aggregations' => $result->getAggregations(),
            'total_count' => $metaInfo['total_count'] ?? null,
            'page_info' => [
                'page_size' => $metaInfo['page_size'] ?? null,
                'current_page' => $metaInfo['current_page'] ?? null,
                'total_pages' => $metaInfo['total_pages'] ?? null,
            ],
            // for backward compatibility: support "filters" field
            'layer_type' => isset($request->getArgs()['search'])
                ? Resolver::CATALOG_LAYER_SEARCH
                : Resolver::CATALOG_LAYER_CATEGORY,
        ];
    }
}
