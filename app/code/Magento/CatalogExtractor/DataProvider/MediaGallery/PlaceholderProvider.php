<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExtractor\DataProvider\MediaGallery;

use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Image Placeholder provider
 * TODO:: remove this class after ENGCOM-6604 issue will be fixed
 */
class PlaceholderProvider
{
    const DEFAULT_THEME_PATH = 'frontend/Magento/blank';

    /**
     * @var PlaceholderFactory
     */
    private $placeholderFactory;

    /**
     * @var AssetRepository
     */
    private $assetRepository;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * Placeholder constructor.
     * @param PlaceholderFactory $placeholderFactory
     * @param AssetRepository $assetRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ThemeProviderInterface $themeProvider
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        AssetRepository $assetRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ThemeProviderInterface $themeProvider
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->assetRepository = $assetRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->themeProvider = $themeProvider;
    }

    /**
     * Get placeholder
     *
     * @param string $imageType
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPlaceholder(string $imageType): string
    {
        $imageAsset = $this->placeholderFactory->create(['type' => $imageType]);

        // check if placeholder defined in config
        if ($imageAsset->getFilePath()) {
            return $imageAsset->getUrl();
        }

        $themeData = $this->getThemeData();
        return $this->assetRepository->createAsset(
            "Magento_Catalog::images/product/placeholder/{$imageType}.jpg",
            $themeData
        )->getUrl();
    }

    /**
     * Get theme model
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getThemeData(): array
    {
        $themeId = $this->scopeConfig->getValue(
            DesignInterface::XML_PATH_THEME_ID,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = null !== $themeId ? $this->themeProvider->getThemeById($themeId)
            : $this->themeProvider->getThemeByFullPath(self::DEFAULT_THEME_PATH);

        $data = $theme->getData();
        $data['themeModel'] = $theme;

        return $data;
    }
}
