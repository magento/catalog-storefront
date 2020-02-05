<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\MediaGallery\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Build Select for fetch media entities data
 */
class MediaGalleryQueryBuilder
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ReadHandler
     */
    private $galleryReadHandler;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery
     */
    private $galleryResourceModel;

    /**
     * @param MetadataPool $metadataPool
     * @param \Magento\Catalog\Model\Product\Gallery\ReadHandler $galleryReadHandler
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $galleryResourceModel
     */
    public function __construct(
        MetadataPool $metadataPool,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $galleryReadHandler,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $galleryResourceModel
    ) {
        $this->metadataPool = $metadataPool;
        $this->galleryReadHandler = $galleryReadHandler;
        $this->galleryResourceModel = $galleryResourceModel;
    }

    /**
     * Form and return query to get product media gallery entities for given product ids
     *
     * @param int[] $productIds
     * @param int $storeId
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function build(array $productIds, int $storeId): Select
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $entityTableName = $metadata->getEntityTable();

        $gallerySelect = $this->galleryResourceModel->createBatchBaseSelect(
            $storeId,
            $this->galleryReadHandler->getAttribute()->getAttributeId()
        );
        $gallerySelect->reset(Select::ORDER);
        $gallerySelect->joinInner(
            ['product_entity' => $entityTableName],
            \sprintf('product_entity.%1$s = entity.%1$s', $linkField),
            ['product_id' => 'product_entity.entity_id']
        );
        $gallerySelect->where('product_entity.entity_id IN (?)', $productIds);

        return $gallerySelect;
    }
}
