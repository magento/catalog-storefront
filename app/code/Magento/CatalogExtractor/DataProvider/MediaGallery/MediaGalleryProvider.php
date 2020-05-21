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
 *  'media_gallery' => [
 *     'url',
 *     'label',
 *     'video_content'
 *   ]
 */
class MediaGalleryProvider implements DataProviderInterface
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

        $attributesMapping = $this->getAttributesMapping();
        $connection = $this->resourceConnection->getConnection();
        $statement = $connection->query(
            $this->galleryQuery->build($productIds, $storeId)
        );

        while ($data = $statement->fetch()) {
            $this->convertAttributesToOutputFormat($output, $data['product_id'], $data, $attributesMapping);
        }
        return $output;
    }

    /**
     * Get video content
     *
     * @param array $image
     * @return array|null
     */
    private function getVideoContent(array $image): ?array
    {
        $filterCallback = function ($value, $field) {
            return !empty($value) && \strpos($field, 'video_') === 0;
        };
        $videoContent = \array_filter($image, $filterCallback, ARRAY_FILTER_USE_BOTH);

        if ($videoContent) {
            $videoContent['media_type'] = $image['media_type'];
        }
        return $videoContent ?: null;
    }

    /**
     * Get mapping for output attributes
     *
     * @return array
     */
    private function getAttributesMapping(): array
    {
        $attributeName = 'media_gallery';
        $attributesMapping = [];
        $attributesMapping[$attributeName]['url'] = function ($item) use ($attributeName) {
            return $this->imageUrlResolver->resolve($item['file'] ?? '', $attributeName);
        };
        $attributesMapping[$attributeName]['label'] = 'label';
        $attributesMapping[$attributeName]['position'] = 'position';
        $attributesMapping[$attributeName]['video_content'] = function ($item) {
            return $this->getVideoContent($item);
        };
        $attributesMapping[$attributeName]['media_type'] = 'media_type';

        return $attributesMapping;
    }

    /**
     * Convert attributes received from storage to output format
     *
     * @param array $output
     * @param int $productId
     * @param array $item
     * @param array $attributesMapping
     * @return array
     */
    private function convertAttributesToOutputFormat(&$output, $productId, array $item, array $attributesMapping): array
    {
        foreach ($attributesMapping as $outputAttribute => $fieldData) {
            $data = [];
            foreach ($fieldData as $outputField => $attribute) {
                $outputValue = \is_callable($attribute) ? $attribute($item) : ($item[$attribute] ?? '');
                if (null !== $outputValue) {
                    $data[$outputField] = $outputValue;
                }
            }
            $output[$productId][$outputAttribute][] = $data;
        }
        return $output;
    }
}
