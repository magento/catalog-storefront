<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product\Simple;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ImageArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\VideoArrayMapper;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for simple product media data exporter
 */
class MediaGalleryTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    private const TEST_SKU = 'simple';
    private const STORE_CODE = 'default';

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var ProductsGetRequestInterface
     */
    private $productsGetRequestInterface;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ArrayUtils
     */
    private $arrayUtils;

    /**
     * @var VideoArrayMapper
     */
    private $videoArrayMapper;

    /**
     * @var ImageArrayMapper
     */
    private $imageArrayMapper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayUtils = Bootstrap::getObjectManager()->create(ArrayUtils::class);
        $this->videoArrayMapper = Bootstrap::getObjectManager()->create(VideoArrayMapper::class);
        $this->imageArrayMapper = Bootstrap::getObjectManager()->create(ImageArrayMapper::class);
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
    }

    /**
     * Validate video data
     *
     * @param array $videoDataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_media_gallery_entries.php
     * @dataProvider getVideoDataProvider
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testVideoData(array $videoDataProvider) : void
    {
        $product = $this->productRepository->get(self::TEST_SKU);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['videos']);

        $catalogServiceItems = $this->catalogService->getProducts($this->productsGetRequestInterface)->getItems();

        $this->assertNotEmpty($catalogServiceItems);
        $item = \array_shift($catalogServiceItems);
        $actualData = [];

        foreach ($item->getVideos() as $video) {
            $actualData[] = $this->videoArrayMapper->convertToArray($video);
        }

        $diff = $this->arrayUtils->recursiveDiff($videoDataProvider, $actualData);
        self::assertEquals([], $diff, 'Actual response data doesn\'t equal to expected data');

        $mediaBaseUrl = $this->storeManager->getStore(self::STORE_CODE)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $productGalleryEntries = $product->getMediaGalleryEntries();
        $galleryEntry = \array_shift($productGalleryEntries);

        self::assertEquals(
            $mediaBaseUrl . 'catalog/product' . $galleryEntry->getFile(),
            $actualData[0]['preview']['url']
        );
    }

    /**
     * Get video data provider
     *
     * @return array
     */
    public function getVideoDataProvider() : array
    {
        return [
            'videoData' => [
                'item' => [
                    [
                        'preview' => [
                            'label' => 'Video Label',
                            'roles' => [
                                'image',
                                'small_image',
                                'thumbnail',
                                'swatch_image',
                            ],
                        ],
                        'video' => [
                            'video_provider' => 'youtube',
                            'video_url' => 'http://www.youtube.com/v/tH_2PFNmWoga',
                            'video_title' => 'Video title',
                            'video_description' => 'Video description',
                            'video_metadata' => 'Video Metadata',
                            'media_type' => 'external-video',
                        ],
                        'sort_order' => '2',
                    ],
                ],
            ],
        ];
    }

    /**
     * Validate image data
     *
     * @param array $imageDataProvider
     *
     * @magentoDataFixture Magento_CatalogStorefront::Test/Api/Product/Simple/_files/product_with_disabled_image.php
     * @dataProvider getImageDataProvider
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testImageData(array $imageDataProvider) : void
    {
        $product = $this->productRepository->get(self::TEST_SKU);

        $this->productsGetRequestInterface->setIds([$product->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes(['images']);

        $catalogServiceItems = $this->catalogService->getProducts($this->productsGetRequestInterface)->getItems();

        $this->assertNotEmpty($catalogServiceItems);
        $item = \array_shift($catalogServiceItems);
        $actualData = [];

        foreach ($item->getImages() as $image) {
            $actualData[] = $this->imageArrayMapper->convertToArray($image);
        }

        $diff = $this->arrayUtils->recursiveDiff($imageDataProvider, $actualData);
        self::assertEquals([], $diff, 'Actual response data doesn\'t equal to expected data');

        $mediaBaseUrl = $this->storeManager->getStore(self::STORE_CODE)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $productGalleryEntries = $product->getMediaGalleryEntries();
        $galleryEntry = \array_shift($productGalleryEntries);

        self::assertEquals(
            $mediaBaseUrl . 'catalog/product' . $galleryEntry->getFile(),
            $actualData[0]['resource']['url']
        );
    }

    /**
     * Get video data provider
     *
     * @return array
     */
    public function getImageDataProvider() : array
    {
        return [
            'imageData' => [
                'item' => [
                    [
                        'resource' => [
                            'label' => 'Image Alt Text',
                            'roles' => [
                                'image',
                                'small_image',
                                'thumbnail',
                                'hide_from_pdp',
                            ],
                        ],
                        'sort_order' => '1',
                    ],
                ],
            ],
        ];
    }
}
