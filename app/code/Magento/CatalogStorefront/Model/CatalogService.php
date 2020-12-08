<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\CatalogStorefront\DataProvider\CategoryDataProvider;
use Magento\CatalogStorefront\DataProvider\ProductDataProvider;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetResponse;
use Magento\CatalogStorefrontApi\Api\Data\CategoriesGetResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\CategoryArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\CategoryMapper;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesResponseFactory;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesResponseInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\DeleteProductsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteProductsResponseFactory;
use Magento\CatalogStorefrontApi\Api\Data\DeleteProductsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteProductsResponseInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesResponseFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsResponseFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResult;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetResultInterface;
use Psr\Log\LoggerInterface;

/**
 * Class for retrieving catalog data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class CatalogService implements CatalogServerInterface
{
    /**
     * @var ProductDataProvider
     */
    private $dataProvider;

    /**
     * @var ImportProductsResponseFactory
     */
    private $importProductsResponseFactory;

    /**
     * @var DeleteProductsResponseInterfaceFactory
     */
    private $deleteProductsResponseFactory;

    /**
     * @var DeleteCategoriesResponseInterfaceFactory
     */
    private $deleteCategoriesResponseFactory;

    /**
     * @var ImportCategoriesResponseFactory
     */
    private $importCategoriesResponseFactory;

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var CategoryDataProvider
     */
    private $categoryDataProvider;

    /**
     * @var ProductArrayMapper
     */
    private $productArrayMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CategoryArrayMapper
     */
    private $categoryArrayMapper;

    /**
     * @var ProductMapper
     */
    private $productMapper;

    /**
     * @var CategoryMapper
     */
    private $categoryMapper;

    /**
     * @param ProductDataProvider $dataProvider
     * @param CategoryDataProvider $categoryDataProvider
     * @param ImportProductsResponseFactory $importProductsResponseFactory
     * @param DeleteProductsResponseFactory $deleteProductsResponseFactory
     * @param DeleteCategoriesResponseFactory $deleteCategoriesResponseFactory
     * @param ImportCategoriesResponseFactory $importCategoriesResponseFactory
     * @param CatalogRepository $catalogRepository
     * @param ProductArrayMapper $productArrayMapper
     * @param CategoryArrayMapper $categoryArrayMapper
     * @param ProductMapper $productMapper
     * @param CategoryMapper $categoryMapper
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProductDataProvider $dataProvider,
        CategoryDataProvider $categoryDataProvider,
        ImportProductsResponseFactory $importProductsResponseFactory,
        DeleteProductsResponseFactory $deleteProductsResponseFactory,
        DeleteCategoriesResponseFactory $deleteCategoriesResponseFactory,
        ImportCategoriesResponseFactory $importCategoriesResponseFactory,
        CatalogRepository $catalogRepository,
        ProductArrayMapper $productArrayMapper,
        CategoryArrayMapper $categoryArrayMapper,
        ProductMapper $productMapper,
        CategoryMapper $categoryMapper,
        LoggerInterface $logger
    ) {
        $this->dataProvider = $dataProvider;
        $this->importProductsResponseFactory = $importProductsResponseFactory;
        $this->deleteProductsResponseFactory = $deleteProductsResponseFactory;
        $this->deleteCategoriesResponseFactory = $deleteCategoriesResponseFactory;
        $this->importCategoriesResponseFactory = $importCategoriesResponseFactory;
        $this->catalogRepository = $catalogRepository;
        $this->categoryDataProvider = $categoryDataProvider;
        $this->productArrayMapper = $productArrayMapper;
        $this->categoryArrayMapper = $categoryArrayMapper;
        $this->productMapper = $productMapper;
        $this->categoryMapper = $categoryMapper;
        $this->logger = $logger;
    }

    /**
     * Get requested products
     *
     * @param ProductsGetRequestInterface $request
     * @return ProductsGetResultInterface
     * @throws \Throwable
     */
    public function getProducts(
        ProductsGetRequestInterface $request
    ): ProductsGetResultInterface {
        $result = new ProductsGetResult();

        if (empty($request->getStore()) || $request->getStore() === null) {
            throw new \InvalidArgumentException('Store code is not present in request.');
        }

        if (empty($request->getIds())) {
            return $result;
        }

        $rawItems = $this->dataProvider->fetch(
            $request->getIds(),
            $request->getAttributeCodes(),
            ['store' => $request->getStore()]
        );

        if (count($rawItems) !== count($request->getIds())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Products with the following ids are not found in catalog: %s',
                    implode(', ', array_diff($request->getIds(), array_keys($rawItems)))
                )
            );
        }

        $products = [];
        foreach ($rawItems as $item) {
            $products[] = $this->productMapper->setData($item)->build();
        }

        $result->setItems($products);

        return $result;
    }

    /**
     * Import requested products
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterface $request
     * @return \Magento\CatalogStorefrontApi\Api\Data\ImportProductsResponseInterface
     * phpcs:disable Generic.CodeAnalysis.EmptyStatement
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function importProducts(ImportProductsRequestInterface $request): ImportProductsResponseInterface
    {
        $importProductsResponse = $this->importProductsResponseFactory->create();

        try {
            $storeCode = $request->getStore();
            $productsInElasticFormat = [];

            foreach ($request->getProducts() as $productData) {
                $product = $this->productArrayMapper->convertToArray($productData->getProduct());
                $product['store_code'] = $storeCode;

                if (!empty($productData->getAttributes())) {
                    $product = \array_filter($product, function ($code) use ($productData) {
                        return \in_array($code, $productData->getAttributes());
                    }, ARRAY_FILTER_USE_KEY);

                    $productsInElasticFormat['product'][$storeCode][CatalogRepository::UPDATE][] = $product;
                } else {
                    $productsInElasticFormat['product'][$storeCode][CatalogRepository::SAVE][] = $product;
                }
            }

            $this->catalogRepository->saveToStorage($productsInElasticFormat);

            $importProductsResponse->setMessage('Records imported successfully');
            $importProductsResponse->setStatus(true);
        } catch (\Throwable $e) {
            $message = \sprintf('Cannot process product import, error: "%s"', $e->getMessage());
            $this->logger->error($message, ['exception' => $e]);
            $importProductsResponse->setMessage($message);
            $importProductsResponse->setStatus(false);
        }

        return $importProductsResponse;
    }

    /**
     * Delete products from storage.
     *
     * @param DeleteProductsRequestInterface $request
     * @return DeleteProductsResponseInterface
     */
    public function deleteProducts(DeleteProductsRequestInterface $request): DeleteProductsResponseInterface
    {
        $storeCode = $request->getStore();
        $productsInElasticFormat = [
            'product' => [
                $storeCode => [
                    CatalogRepository::DELETE => $request->getProductIds()
                ]
            ]
        ];

        /** @var \Magento\CatalogStorefrontApi\Api\Data\DeleteProductsResponse $deleteProductsResponse */
        $deleteProductsResponse = $this->deleteProductsResponseFactory->create();

        try {
            $this->catalogRepository->saveToStorage($productsInElasticFormat);

            $deleteProductsResponse->setMessage('Product were removed successfully');
            $deleteProductsResponse->setStatus(true);
        } catch (\Throwable $e) {
            $message = 'Unable to delete products';
            $this->logger->error($message, ['exception' => $e]);
            $deleteProductsResponse->setMessage($message);
            $deleteProductsResponse->setStatus(false);
        }

        return $deleteProductsResponse;
    }

    /**
     * Import requested categories
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesRequestInterface $request
     * @return \Magento\CatalogStorefrontApi\Api\Data\ImportCategoriesResponseInterface
     * phpcs:disable Generic.CodeAnalysis.EmptyStatement
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function importCategories(ImportCategoriesRequestInterface $request): ImportCategoriesResponseInterface
    {
        try {
            $storeCode = $request->getStore();
            $categoriesInElasticFormat = [];

            foreach ($request->getCategories() as $categoryData) {
                $category = $this->categoryArrayMapper->convertToArray($categoryData->getCategory());
                $category['store_code'] = $storeCode;

                if (!empty($categoryData->getAttributes())) {
                    $category = \array_filter($category, function ($code) use ($categoryData) {
                        return \in_array($code, $categoryData->getAttributes());
                    }, ARRAY_FILTER_USE_KEY);

                    $categoriesInElasticFormat['category'][$storeCode][CatalogRepository::UPDATE][] = $category;
                } else {
                    $categoriesInElasticFormat['category'][$storeCode][CatalogRepository::SAVE][] = $category;
                }
            }

            $this->catalogRepository->saveToStorage($categoriesInElasticFormat);

            $importCategoriesResponse = $this->importCategoriesResponseFactory->create();
            $importCategoriesResponse->setMessage('Records imported successfully');
            $importCategoriesResponse->setStatus(true);

            return $importCategoriesResponse;
        } catch (\Exception $e) {
            $message = 'Cannot process categories import: ' . $e->getMessage();
            $this->logger->error($message, ['exception' => $e]);
            $importCategoriesResponse = $this->importCategoriesResponseFactory->create();
            $importCategoriesResponse->setMessage($message);
            $importCategoriesResponse->setStatus(false);

            return $importCategoriesResponse;
        }
    }

    /**
     * Delete categories from storage.
     *
     * @param DeleteCategoriesRequestInterface $request
     * @return DeleteCategoriesResponseInterface
     */
    public function deleteCategories(DeleteCategoriesRequestInterface $request): DeleteCategoriesResponseInterface
    {
        $storeId = $request->getStore();
        $categoriesInElasticFormat = [
            'category' => [
                $storeId => [
                    CatalogRepository::DELETE => $request->getCategoryIds()
                ]
            ]
        ];

        /** @var \Magento\CatalogStorefrontApi\Api\Data\DeleteCategoriesResponse $deleteCategoriesResponse */
        $deleteCategoriesResponse = $this->deleteCategoriesResponseFactory->create();

        try {
            $this->catalogRepository->saveToStorage($categoriesInElasticFormat);

            $deleteCategoriesResponse->setMessage('Category were removed successfully');
            $deleteCategoriesResponse->setStatus(true);
        } catch (\Throwable $e) {
            $message = 'Unable to delete categories';
            $this->logger->error($message, ['exception' => $e]);
            $deleteCategoriesResponse->setMessage($message);
            $deleteCategoriesResponse->setStatus(false);
        }

        return $deleteCategoriesResponse;
    }

    /**
     * Get requested categories
     *
     * @param CategoriesGetRequestInterface $request
     * @return CategoriesGetResponseInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @throws \Throwable
     */
    public function getCategories(
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
            $items[] = $this->categoryMapper->setData($category)->build();
        }

        $result->setItems($items);

        return $result;
    }
}
