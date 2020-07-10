<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontGraphQl\Resolver\Product;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogStorefrontApi\Api\Data\ProductResultContainerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResultInterface;
use Magento\CatalogStorefrontGraphQl\Model\Converter\ObjectToArray;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;

/**
 * Format response from Storefront service
 */
class OutputFormatter
{
    /**
     * Format Storefront output for GraphQL response
     *
     * @param ProductResultContainerInterface $result
     * @param GraphQlInputException $e
     * @param BatchRequestItemInterface $request
     * @param array $additionalInfo
     * @return array
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(
        ProductsGetResultInterface $result,
        GraphQlInputException $e,
        BatchRequestItemInterface $request,
        array $additionalInfo = []
    ) {
        $errors = $additionalInfo['errors'] ?? [];
        if (!empty($errors)) {
            //ad-hoc solution with __() as GraphQlInputException accepts Phrase in construct
            //TODO: change with error holder
            throw new GraphQlInputException(__(\implode('; ', \array_map('\strval', $errors))));
        }
        $converter = new ObjectToArray;
        $items = [];
        foreach ($result->getItems() as $item) {
            $currentResult = $converter->getArray($item);
            $currentResult['entity_id'] = $currentResult['id'];
            $currentResult['description'] = ['html' => $currentResult['description']];
            $currentResult['short_description'] = ['html' => $currentResult['short_description']];
            $currentResult['gift_message_available'] = (int)$currentResult['gift_message_available'];
            if (
                isset($currentResult['only_x_left_in_stock'])
                && (string)$currentResult['only_x_left_in_stock'] == "0"
            ) {
                $currentResult['only_x_left_in_stock'] = null;
            }

            $currentResult['canonical_url'] = empty($currentResult['canonical_url'])
                ? null
                : $currentResult['canonical_url'];

            foreach ($currentResult['options'] as &$option) {
                //Convert simple option types from arrays
                $simpleOptionTypes = ['date_time', 'field', 'area', 'file'];
                if (isset($option['type']) && in_array($option['type'], $simpleOptionTypes)) {
                    $option['value'] = reset($option['value']);
                }
            }
            $currentResult['media_gallery'] = array_map(function (array $item) {
                $item['disabled'] = false;
                return $item;
            }, $currentResult['media_gallery']);

            $currentResult = $this->setDynamicAttributes($currentResult);

            $items[] = $currentResult;
        }
        $metaInfo = $additionalInfo['meta_info'] ?? [];
        $aggregations = $additionalInfo['aggregations'] ?? [];

        return [
            'items' => $items,
            'aggregations' => $aggregations,
            'total_count' => $metaInfo['total_count'] ?? null,
            'page_info' => [
                'page_size' => $metaInfo['page_size'] ?? null,
                'current_page' => $metaInfo['current_page'] ?? null,
                'total_pages' => $metaInfo['total_pages'] ?? null,
            ],
            'search_result' => $additionalInfo['search_result'] ?? null,
            // for backward compatibility: support "filters" field
            'layer_type' => isset($request->getArgs()['search'])
                ? Resolver::CATALOG_LAYER_SEARCH
                : Resolver::CATALOG_LAYER_CATEGORY,
        ];
    }

    /**
     * Set dynamic attributes (product attributes created in admin) into output result
     *
     * @param array $currentResult
     * @return array
     */
    private function setDynamicAttributes(array $currentResult): array
    {
        if (empty($currentResult['dynamic_attributes'])) {
            return $currentResult;
        }

        foreach ($currentResult['dynamic_attributes'] as $attribute) {
            if (!isset($currentResult[$attribute['code']])) {
                $currentResult[$attribute['code']] = $attribute['value'];
            }
        }

        return $currentResult;
    }
}
