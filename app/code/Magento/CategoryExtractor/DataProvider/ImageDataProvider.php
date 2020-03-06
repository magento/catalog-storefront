<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CategoryExtractor\DataProvider;

use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @inheritdoc
 */
class ImageDataProvider implements DataProviderInterface
{
    /**
     * Image attribute code
     */
    private const ATTRIBUTE = 'image';

    /**
     * @var CategoriesProvider
     */
    private $categoriesProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CategoriesProvider $categoriesProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoriesProvider $categoriesProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->categoriesProvider = $categoriesProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(array $categoryIds, array $attributes, array $scopes): array
    {
        $output = [];
        $attribute = !empty($attributes) ? key($attributes) : self::ATTRIBUTE;

        foreach ($this->categoriesProvider->getCategoriesByIds(
            $categoryIds,
            $scopes['store'],
            [$attribute]
        ) as $category) {
            $categoryId = $category->getId();
            $imageUrl = $category->getImage() ?? null;
            if ($imageUrl) {
                /** @var StoreInterface $store */
                $store = $this->storeManager->getStore($scopes['store']);
                $mediaBaseUrl = $store->getBaseUrl(
                    UrlInterface::URL_TYPE_MEDIA
                );
                $pos = strpos($imageUrl, $mediaBaseUrl);
                if ($pos === false) {
                    $imageUrl = rtrim($mediaBaseUrl, '/') . '/' . ltrim($imageUrl, '/');
                }
            }
            $output[$categoryId][$attribute] = $imageUrl;
        }

        return $output;
    }
}
