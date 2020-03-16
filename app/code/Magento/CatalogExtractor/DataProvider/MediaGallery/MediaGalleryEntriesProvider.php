<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\MediaGallery;

use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogExtractor\DataProvider\MediaGallery\Query\MediaGalleryQueryBuilder;

/**
 * Provide data for media gallery:
 *  'media_gallery_entries' => [
 *    'disabled'
 *    'file'
 *    'label'
 *    'position'
 *    'media_type'
 *   ]
 */
class MediaGalleryEntriesProvider implements DataProviderInterface
{
    /**
     * @var MediaGalleryQueryBuilder
     */
    private $galleryQuery;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ImageUrlResolver
     */
    private $imageUrlResolver;

    /**
     * @param MediaGalleryQueryBuilder $galleryQuery
     * @param ImageUrlResolver $imageUrlResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        MediaGalleryQueryBuilder $galleryQuery,
        ImageUrlResolver $imageUrlResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->galleryQuery = $galleryQuery;
        $this->imageUrlResolver = $imageUrlResolver;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function fetch(array $productIds, array $attributes, array $scopes): array
    {
        $output = [];
        $storeId = (int)$scopes['store'];
        $attributeName = array_key_first($attributes);
        $connection = $this->resourceConnection->getConnection();
        $statement = $connection->query(
            $this->galleryQuery->build($productIds, $storeId)
        );

        while ($data = $statement->fetch()) {
            $output[$data['product_id']][$attributeName][] = $this->convertAttributesToOutputFormat(
                $data,
                $attributes[$attributeName]
            );
        }
        return $output;
    }

    /**
     * Convert attributes received from storage to output format
     *
     * @param array $item
     * @param array $attributes
     * @return array
     */
    private function convertAttributesToOutputFormat(array $item, array $attributes): array
    {
        $output = [];
        foreach ($attributes as $requestedAttribute) {
            $output[$requestedAttribute] = $item[$requestedAttribute] ?? null;
        }
        return $output;
    }
}
