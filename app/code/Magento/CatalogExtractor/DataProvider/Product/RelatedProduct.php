<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Related Products Provider
 */
class RelatedProduct
{
    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @param LinkFactory $linkFactory
     * @param MetadataPool|null $metadataPool
     * @param HydratorPool|null $hydratorPool
     */
    public function __construct(
        LinkFactory $linkFactory,
        ?MetadataPool $metadataPool = null,
        ?HydratorPool $hydratorPool = null
    ) {
        $this->linkFactory = $linkFactory;
        $this->metadataPool = $metadataPool
            ?? \Magento\Framework\App\ObjectManager::getInstance()->get(MetadataPool::class);
        $this->hydratorPool = $hydratorPool
            ?? \Magento\Framework\App\ObjectManager::getInstance()->get(HydratorPool::class);
    }

    /**
     * Related Products Data
     *
     * @param Product $product
     * @param array $fields
     * @param int $linkType
     * @return array
     */
    public function getData(Product $product, array $fields, int $linkType): array
    {
        $relatedProducts = $this->getRelatedProducts($product, $fields, $linkType);

        $productsData = [];
        foreach ($relatedProducts as $relatedProduct) {
            $productData = $relatedProduct->getData();
            $productData['model'] = $relatedProduct;
            $productsData[] = $productData;
        }
        return $productsData;
    }

    /**
     * Get Related Products
     *
     * @param Product $product
     * @param array $fields
     * @param int $linkType
     * @return Product[]
     */
    private function getRelatedProducts(Product $product, array $fields, int $linkType): array
    {
        /** @var Link $link */
        $link = $this->linkFactory->create(['data' => ['link_type_id' => $linkType]]);

        $collection = $link->getProductCollection();
        $collection->setIsStrongMode();
        foreach ($fields as $field) {
            $collection->addAttributeToSelect($field);
        }
        $collection->setProduct($product);

        return $collection->getItems();
    }

    /**
     * Get related product IDs for given products.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @param int $linkType
     * @return string[][] keys - IDs, values - list of linked product IDs.
     */
    public function getRelations(array $products, int $linkType): array
    {
        //Load all links
        /** @var Link $link */
        $link = $this->linkFactory->create(['data' => ['link_type_id' => $linkType]]);
        $collection = $link->getLinkCollection();
        $collection->addFieldToFilter('product_id', ['in' => array_keys($products)]);
        $collection->addLinkTypeIdFilter();

        //Prepare map
        $map = [];
        /** @var Link $item */
        foreach ($collection as $item) {
            $productId = $item->getProductId();
            if (!array_key_exists($productId, $map)) {
                $map[$productId] = [];
            }
            $map[$productId][] = $item->getLinkedProductId();
        }

        return $map;
    }
}
