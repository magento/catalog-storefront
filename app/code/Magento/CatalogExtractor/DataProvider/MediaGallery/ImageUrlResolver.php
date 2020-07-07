<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\MediaGallery;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\Framework\App\Area;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\DesignInterface;

/**
 * Get image URL
 */
class ImageUrlResolver
{
    /**
     * @var ImageFactory
     */
    private $productImageFactory;

    /**
     * @var null|Image
     */
    private $image;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var DesignInterface
     */
    private $themeDesign;

    /**
     * @param ImageFactory $productImageFactory
     * @param AssetRepository $assetRepository
     * @param DesignInterface $themeDesign
     */
    public function __construct(
        ImageFactory $productImageFactory,
        AssetRepository $assetRepository,
        DesignInterface $themeDesign
    ) {
        $this->productImageFactory = $productImageFactory;
        $this->assetRepository = $assetRepository;
        $this->themeDesign = $themeDesign;
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
            $params = [
                'area' => Area::AREA_FRONTEND,
                'themeId' => $this->themeDesign->getConfigurationDesignTheme(Area::AREA_FRONTEND),
            ];

            return $this->assetRepository->getUrlWithParams(
                "Magento_Catalog::images/product/placeholder/{$imageType}.jpg",
                $params
            );
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
