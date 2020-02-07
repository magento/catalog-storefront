<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\Options;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface as ProductCustomOptionInterfaceAlias;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Product options data provider, used for GraphQL resolver processing.
 */
class CustomizableOptions implements DataProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        $output = [];
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($products as $product) {
            $output[$product->getId()]['options'] = $this->getOptions($product);
        }

        return $output;
    }

    /**
     * Returns options of product.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    private function getOptions(\Magento\Catalog\Api\Data\ProductInterface $product): array
    {
        $options = [];

        if (!empty($product->getOptions())) {
            /** @var ProductCustomOptionInterfaceAlias $option */
            foreach ($product->getOptions() as $key => $option) {
                $options[$key] = $option->getData();
                $options[$key]['required'] = $option->getIsRequire();
                $options[$key]['product_sku'] = $option->getProductSku();
                $options[$key]['value'] = $this->processOptionValues($option);
            }
        }

        return $options;
    }

    /**
     * Process option values.
     *
     * @param ProductCustomOptionInterfaceAlias $option
     * @return array
     */
    private function processOptionValues(
        ProductCustomOptionInterfaceAlias $option
    ): array {
        $resultValues = [];
        $values = $option->getValues() ?: [];

        if (!empty($values)) {
            /** @var Option\Value $value */
            foreach ($values as $valueKey => $value) {
                $resultValues[$valueKey] = $value->getData();
                $resultValues[$valueKey]['price_type'] = strtoupper($value->getPriceType() ?? 'DYNAMIC');
            }
        } else {
            $resultValues = $option->getData();
            $resultValues['price_type'] = strtoupper($option->getPriceType() ?? 'DYNAMIC');
        }

        return $resultValues;
    }
}
