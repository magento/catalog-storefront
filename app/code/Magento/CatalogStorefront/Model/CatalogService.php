<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\Breadcrumb;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\Category;
use Magento\CatalogStorefrontApi\Api\Data\CategoryInterface;
use Magento\CatalogStorefrontApi\Api\Data\Image;
use Magento\CatalogStorefrontApi\Api\Data\ProductInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResult;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResultInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsResponseFactory;
use Magento\CatalogStorefront\DataProvider\ProductDataProvider;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetResponse;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\CatalogStorefront\Model\CatalogRepository;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetRequestInterface;
use Magento\CatalogStorefront\DataProvider\CategoryDataProvider;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesResponseFactory;

/**
 * @inheritdoc
 */
class CatalogService implements CatalogServerInterface
{
    private const ROOT_CATEGORY_ID = 1;

    /**
     * @var ProductDataProvider
     */
    private $dataProvider;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ImportProductsResponseFactory
     */
    private $importProductsResponseFactory;

    /**
     * @var ImportCategoriesResponseFactory
     */
    private $importCategoriesResponseFactory;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var CategoryDataProvider
     */
    private $categoryDataProvider;

    /**
     * CatalogService constructor.
     *
     * @param ProductDataProvider $dataProvider
     * @param DataObjectHelper $dataObjectHelper
     * @param ImportProductsResponseFactory $importProductsResponseFactory
     * @param ImportCategoriesResponseFactory $importCategoriesResponseFactory
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param CatalogRepository $catalogRepository
     * @param CategoryDataProvider $categoryDataProvider
     */
    public function __construct(
        ProductDataProvider $dataProvider,
        DataObjectHelper $dataObjectHelper,
        ImportProductsResponseFactory $importProductsResponseFactory,
        ImportCategoriesResponseFactory $importCategoriesResponseFactory,
        ServiceOutputProcessor $serviceOutputProcessor,
        CatalogRepository $catalogRepository,
        CategoryDataProvider $categoryDataProvider
    ) {
        $this->dataProvider = $dataProvider;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->importProductsResponseFactory = $importProductsResponseFactory;
        $this->importCategoriesResponseFactory = $importCategoriesResponseFactory;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->catalogRepository = $catalogRepository;
        $this->categoryDataProvider = $categoryDataProvider;
    }

    public function GetProducts(
        ProductsGetRequestInterface $request
    ): ProductsGetResultInterface {
        if (is_null($request->getStore()) || empty($request->getStore())) {
            throw new \InvalidArgumentException(
                __('Store id is not present in Search Criteria. Please add missing info.')
            );
        }
        $result = new ProductsGetResult();
        $products = [];
        if (!empty($request->getIds())) {
            $rawItems = $this->dataProvider->fetch(
                $request->getIds(),
                $request->getAttributeCodes(),
                ['store' => $request->getStore()]
            );

            foreach ($rawItems as $item) {
                $item = $this->cleanUpNullValues($item);
                $item['description'] = $item['description']['html'] ?? "";
                //Convert option values to unified array format
                if (!empty($item['options'])) {
                    foreach ($item['options'] as &$option) {
                        $firstValue = reset($option['value']);
                        if (!is_array($firstValue)) {
                            $option['value'] = [0 => $option['value']];
                            continue;
                        }
                    }
                }
                $product = new \Magento\CatalogStorefrontApi\Api\Data\Product();
                $this->dataObjectHelper->populateWithArray($product, $item, ProductInterface::class);
                $product = $this->setImage('image', $item, $product);
                $product = $this->setImage('small_image', $item, $product);
                $product = $this->setImage('thumbnail', $item, $product);

                $products[] = $product;
            }
        }
        $result->setItems($products);

        return $result;
    }

    /**
     * Unset null values in provided array recursively
     *
     * @param array $array
     * @return array
     */
    private function cleanUpNullValues(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_null($value) || $value === "") {
                continue;
            }

            $result[$key] = is_array($value) ? $this->cleanUpNullValues($value) : $value;
        }
        return $result;
    }

    private function setImage(string $key, array $rawData, ProductInterface $product): ProductInterface
    {
        if (empty($rawData[$key])) {
            return $product;
        }

        $image = new Image();
        $image->setUrl($rawData[$key]['url'] ?? "");
        $image->setLabel($rawData[$key]['label'] ?? "");
        $parts = explode('_', $key);
        $parts = array_map("ucfirst", $parts);
        $methodName = 'set' . implode('', $parts);

        $product->$methodName($image);
        return $product;
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterface $request
     * @return \Magento\CatalogStorefrontApi\Api\Data\ImportProductsResponseInterface
     */
    public function ImportProducts(ImportProductsRequestInterface $request): ImportProductsResponseInterface
    {
        try {
            $convertedRequest = $this->serviceOutputProcessor->convertValue(
                $request,
                ImportProductsRequestInterface::class
            );
            $products = $convertedRequest['products'];
            $storeId = $convertedRequest['store'];
            $productsInElasticFormat = [];
            foreach ($products as $product) {
                $productInElasticFormat = $product;
                if (empty($productInElasticFormat)) {
                    // TODO: Implemente Delete
                    // $dataPerType[$entity->getEntityType()][$entity->getStoreId()][self::DELETE][] = $entity->getEntityId();
                } else {
                    $productInElasticFormat['store_id'] = $storeId;
                    foreach ($productInElasticFormat['dynamic_attributes'] as $dynamicAttribute) {
                        $productInElasticFormat[$dynamicAttribute['code']] = $dynamicAttribute['value'];
                    }
                    $productInElasticFormat['short_description'] = ['html' => $productInElasticFormat['short_description']];
                    $productInElasticFormat['description'] = ['html' => $productInElasticFormat['description']];
                    unset($productInElasticFormat['dynamic_attributes']);

                    $productsInElasticFormat['product'][$storeId]['save'][] = $productInElasticFormat;
                }
            }
            $this->catalogRepository->saveToStorage($productsInElasticFormat);

            $importProductsResponse = $this->importProductsResponseFactory->create();
            $importProductsResponse->setMessage('Records imported successfully');
            $importProductsResponse->setStatus(true);
            return $importProductsResponse;
        } catch (\Exception $e) {
            // TODO: Hide real message in production
            $importProductsResponse = $this->importProductsResponseFactory->create();
            $importProductsResponse->setMessage($e->getMessage());
            $importProductsResponse->setStatus(false);
            return $importProductsResponse;
        }
    }

    /**
     * @param \Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterface $request
     * @return \Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesResponseInterface
     */
    public function ImportCategories(ImportCategoriesRequestInterface $request): ImportCategoriesResponseInterface
    {
        try {
            $convertedRequest = $this->serviceOutputProcessor->convertValue(
                $request,
                ImportCategoriesRequestInterface::class
            );
            $categories = $convertedRequest['categories'];
            $storeId = $convertedRequest['store'];
            $categoriesInElasticFormat = [];

            foreach ($categories as $category) {
                if (isset($category['id']) && ($category['id'] === self::ROOT_CATEGORY_ID)) {
                    // Protect root category from modifications
                    continue;
                }
                $categoryInElasticFormat = $category;
                if (empty($categoryInElasticFormat)) {
                    // TODO: Implemente Delete
                    // $dataPerType[$entity->getEntityType()][$entity->getStoreId()][self::DELETE][] = $entity->getEntityId();
                } else {
                    $categoryInElasticFormat['store_id'] = $storeId;
                    foreach ($categoryInElasticFormat['dynamic_attributes'] as $dynamicAttribute) {
                        $categoryInElasticFormat[$dynamicAttribute['code']] = $dynamicAttribute['value'];
                    }
                    unset($categoryInElasticFormat['dynamic_attributes']);

                    // TODO: Check if any of the following is required
                    $categoryInElasticFormat['is_active'] = $categoryInElasticFormat['is_active'] ? '1' : '0';
                    $categoryInElasticFormat['is_anchor'] = $categoryInElasticFormat['is_anchor'] ? '1' : '0';
                    $categoryInElasticFormat['include_in_menu'] = $categoryInElasticFormat['include_in_menu'] ? '1' : '0';
                    $categoryInElasticFormat['store_id'] = (int)$categoryInElasticFormat['store_id'];

                    $categoryInElasticFormat['url_path'] = !empty($categoryInElasticFormat['url_path']) ? $categoryInElasticFormat['url_path'] :  null;
                    $categoryInElasticFormat['image'] = !empty($categoryInElasticFormat['image']) ? $categoryInElasticFormat['image'] : null;
                    $categoryInElasticFormat['description'] = !empty($categoryInElasticFormat['description']) ? $categoryInElasticFormat['description'] : null;
                    $categoryInElasticFormat['canonical_url'] = !empty($categoryInElasticFormat['canonical_url']) ? $categoryInElasticFormat['canonical_url'] : null;

                    $categoryInElasticFormat['product_count'] = (string)$categoryInElasticFormat['product_count'];
                    $categoryInElasticFormat['children_count'] = (string)$categoryInElasticFormat['children_count'];
                    $categoryInElasticFormat['level'] = (string)$categoryInElasticFormat['level'];
                    $categoryInElasticFormat['position'] = (string)$categoryInElasticFormat['position'];
                    $categoryInElasticFormat['id'] = (int)$categoryInElasticFormat['id'];
                    if (isset($categoryInElasticFormat['parent_id']) && empty($categoryInElasticFormat['parent_id'])) {
                        unset($categoryInElasticFormat['parent_id']);
                    }
                    if (isset($categoryInElasticFormat['display_mode']) && empty($categoryInElasticFormat['display_mode'])) {
                        unset($categoryInElasticFormat['display_mode']);
                    }
                    if (isset($categoryInElasticFormat['default_sort_by']) && empty($categoryInElasticFormat['default_sort_by'])) {
                        unset($categoryInElasticFormat['default_sort_by']);
                    }

                    $categoriesInElasticFormat['category'][$storeId]['save'][] = $categoryInElasticFormat;
                }
            }
            $this->catalogRepository->saveToStorage($categoriesInElasticFormat);

            $importCategoriesResponse = $this->importCategoriesResponseFactory->create();
            $importCategoriesResponse->setMessage('Records imported successfully');
            $importCategoriesResponse->setStatus(true);
            return $importCategoriesResponse;
        } catch (\Exception $e) {
            // TODO: Hide real message in production
            $importCategoriesResponse = $this->importCategoriesResponseFactory->create();
            $importCategoriesResponse->setMessage($e->getMessage());
            $importCategoriesResponse->setStatus(false);
            return $importCategoriesResponse;
        }
    }

    public function GetCategories(
        CategoriesGetRequestInterface $request
    ): CategoriesGetResponseInterface {
        $result = new CategoriesGetResponse();

        $categories = $this->categoryDataProvider->fetch(
            $request->getIds(),
            \array_merge($request->getAttributeCodes(), ['is_active']),
            ['store' => $request->getStore()]
        );
        $items = [];
        foreach ($categories as $category) {
            $item = new Category();

            $category = $this->convertKeyToString('image', $category);
            $category = $this->convertKeyToString('canonical_url', $category);
            $category = $this->convertKeyToString('description', $category);

            $this->dataObjectHelper->populateWithArray($item, $category, CategoryInterface::class);

            $breadcrumbsData = $category['breadcrumbs'] ?? [];
            if ($breadcrumbsData) {
                $breadcrumbs = [];
                foreach ($breadcrumbsData as $breadcrumbData) {
                    $breadcrumb = new Breadcrumb();
                    $breadcrumb->setCategoryId($breadcrumbData['category_id']);
                    $breadcrumb->setCategoryLevel((int)$breadcrumbData['category_level']);
                    $breadcrumb->setCategoryName($breadcrumbData['category_name']);
                    $breadcrumb->setCategoryUrlKey($breadcrumbData['category_url_key']);
                    $breadcrumb->setCategoryUrlPath($breadcrumbData['category_url_path']);
                    $breadcrumbs[] = $breadcrumb;
                }
            }
            $items[] = $item;
        }
        $result->setItems($items);

        return $result;
    }

    /**
     * Converts value of array to string type for provided key
     *
     * @param string $key
     * @param array $data
     * @return array
     */
    private function convertKeyToString(string $key, array $data): array
    {
        if (!array_key_exists($key, $data)) {
            return $data;
        }

        if (!is_string($data[$key])) {
            $data[$key] = "";
        }
        return $data;
    }
}
