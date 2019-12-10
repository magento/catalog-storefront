<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogProduct\DataProvider\MediaGallery;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\ImageFactory;

/**
 * Get image URL
 */
class ImageUrlResolver
{
    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var ImageFactory
     */
    private $productImageFactory;

    /**
     * @var null|Image
     */
    private $image;

    /**
     * @param ImageFactory $productImageFactory
     * @param Image $imageHelper
     */
    public function __construct(
        ImageFactory $productImageFactory,
        Image $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
        $this->productImageFactory = $productImageFactory;
    }

    /**
     * Get image URL for specified $imagePath. Placeholder of $imageType is returned in case of no imagePath
     *
     * @param string $imagePath
     * @param string $imageType
     * @return string
     * @throws \Exception
     */
    public function resolve(string $imagePath, string $imageType): string
    {
        $image = $this->getImage();
        $image->setDestinationSubdir($imageType)
            ->setBaseFile($imagePath);

        if ($image->isBaseFilePlaceholder()) {
            return $this->imageHelper->getDefaultPlaceholderUrl($imageType);
        }

        return $image->getUrl();
    }

    /**
     * Get image instance
     *
     * @return Image|\Magento\Catalog\Model\Product\Image
     */
    private function getImage()
    {
        if (null === $this->image) {
            $this->image = $this->productImageFactory->create();
        }
        return $this->image;
    }
}
