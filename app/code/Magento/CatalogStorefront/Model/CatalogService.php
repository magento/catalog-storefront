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
use Magento\CatalogStorefront\Model\MessageBus\Consumer as ElasticAdapter;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetRequestInterface;
use Magento\CatalogStorefront\DataProvider\CategoryDataProvider;

/**
 * @inheritdoc
 */
class CatalogService implements CatalogServerInterface
{
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
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var ElasticAdapter
     */
    private $elasticAdapter;

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
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param ElasticAdapter $elasticAdapter
     * @param CategoryDataProvider $categoryDataProvider
     */
    public function __construct(
        ProductDataProvider $dataProvider,
        DataObjectHelper $dataObjectHelper,
        ImportProductsResponseFactory $importProductsResponseFactory,
        ServiceOutputProcessor $serviceOutputProcessor,
        ElasticAdapter $elasticAdapter,
        CategoryDataProvider $categoryDataProvider
    ) {
        $this->dataProvider = $dataProvider;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->importProductsResponseFactory = $importProductsResponseFactory;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->elasticAdapter = $elasticAdapter;
        $this->categoryDataProvider = $categoryDataProvider;
    }

    public function GetProducts(
        ProductsGetRequestInterface $request
    ): ProductsGetResultInterface {
        if (is_null($request->getStore()) || empty($request->getStore())) {
            return $this->processErrors([_('Store id is not present in Search Criteria. Please add missing info.')]);
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
            if (is_null($value)) {
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
            $this->elasticAdapter->saveToStorage($productsInElasticFormat);

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
