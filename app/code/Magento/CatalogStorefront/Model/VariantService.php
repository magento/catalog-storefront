<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\CatalogStorefront\DataProvider\ProductVariantsDataProvider;
use Magento\CatalogStorefrontApi\Api\Data\DeleteVariantsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteVariantsResponseFactory;
use Magento\CatalogStorefrontApi\Api\Data\DeleteVariantsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportVariantsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportVariantsResponseFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportVariantsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\OptionSelectionRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterfaceFactory;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantResponse;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantResponseInterface;
use Magento\CatalogStorefrontApi\Api\VariantServiceServerInterface;
use Magento\Framework\Exception\RuntimeException;
use Psr\Log\LoggerInterface;

/**
 * Class for retrieving product variants data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class VariantService implements VariantServiceServerInterface
{
    /**
     * Temporary store placeholder
     * TODO: Adapt to work without store code https://github.com/magento/catalog-storefront/issues/417 and remove this constant
     */
    public const EMPTY_STORE_CODE = '';

    /**
     * Product enabled status.
     */
    private const PRODUCT_STATUS_ENABLED = 'Enabled';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ImportVariantsResponseFactory
     */
    private $importVariantsResponseFactory;

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var DeleteVariantsResponseFactory
     */
    private $deleteVariantsResponseFactory;

    /**
     * @var ProductVariantsDataProvider
     */
    private $productVariantsDataProvider;

    /**
     * @var ProductVariantMapper
     */
    private $productVariantMapper;

    /**
     * @var CatalogService
     */
    private $catalogService;

    /**
     * @var ProductsGetRequestInterfaceFactory
     */
    private $productsGetRequestInterfaceFactory;

    /**
     * @param ImportVariantsResponseFactory $importVariantsResponseFactory
     * @param DeleteVariantsResponseFactory $deleteVariantsResponseFactory
     * @param LoggerInterface $logger
     * @param CatalogRepository $catalogRepository
     * @param ProductVariantsDataProvider $productVariantsDataProvider
     * @param ProductVariantMapper $productVariantMapper
     * @param CatalogService $catalogService
     * @param ProductsGetRequestInterfaceFactory $productsGetRequestInterfaceFactory
     */
    public function __construct(
        ImportVariantsResponseFactory $importVariantsResponseFactory,
        DeleteVariantsResponseFactory $deleteVariantsResponseFactory,
        LoggerInterface $logger,
        CatalogRepository $catalogRepository,
        ProductVariantsDataProvider $productVariantsDataProvider,
        ProductVariantMapper $productVariantMapper,
        CatalogService $catalogService,
        ProductsGetRequestInterfaceFactory $productsGetRequestInterfaceFactory
    ) {
        $this->importVariantsResponseFactory = $importVariantsResponseFactory;
        $this->logger = $logger;
        $this->catalogRepository = $catalogRepository;
        $this->deleteVariantsResponseFactory = $deleteVariantsResponseFactory;
        $this->productVariantsDataProvider = $productVariantsDataProvider;
        $this->productVariantMapper = $productVariantMapper;
        $this->catalogService = $catalogService;
        $this->productsGetRequestInterfaceFactory = $productsGetRequestInterfaceFactory;
    }

    /**
     * Import requested variants in storage
     *
     * @param ImportVariantsRequestInterface $request
     * @return ImportVariantsResponseInterface
     */
    public function ImportProductVariants(ImportVariantsRequestInterface $request): ImportVariantsResponseInterface
    {
        try {
            $variantsInElasticFormat = [];
            foreach ($request->getVariants() as $variantData) {
                $optionValues = $variantData->getOptionValues();
                $id = $variantData->getId();
                $explodedId = explode('/', $id);
                $parentId = $explodedId[1];
                $childId = $explodedId[2];
                foreach ($optionValues as $optionValue) {
                    preg_match('/(?<=:)(.*)(?=\/)/', $optionValue, $match);
                    $attrCode = $match[0];
                    $variant = [
                        '_id' => $id . '/' . $attrCode,
                        'id' => $id,
                        'option_value' => $optionValue,
                        'product_id' => $childId,
                        'parent_id' => $parentId
                    ];
                    //TODO: Adapt to work without store code https://github.com/magento/catalog-storefront/issues/417
                    $variantsInElasticFormat['product_variant'][self::EMPTY_STORE_CODE][CatalogRepository::SAVE][] =
                        $variant;
                }
            }

            $this->catalogRepository->saveToStorage($variantsInElasticFormat);
            $importVariantsResponse = $this->importVariantsResponseFactory->create();
            $importVariantsResponse->setMessage('Records imported successfully');
            $importVariantsResponse->setStatus(true);

            return $importVariantsResponse;
        } catch (\Exception $e) {
            $message = 'Cannot process product variants import: ' . $e->getMessage();
            $this->logger->error($message, ['exception' => $e]);
            $importCategoriesResponse = $this->importVariantsResponseFactory->create();
            $importCategoriesResponse->setMessage($message);
            $importCategoriesResponse->setStatus(false);
            return $importCategoriesResponse;
        }
    }

    /**
     * Delete product variants from storage.
     *
     * @param DeleteVariantsRequestInterface $request
     * @return DeleteVariantsResponseInterface
     */
    public function DeleteProductVariants(DeleteVariantsRequestInterface $request): DeleteVariantsResponseInterface
    {
        $deleteFields = \array_map(function ($id) {
            return ['id' => $id];
        }, $request->getId());

        $variantsInElasticFormat = [
            'product_variant' => [
                //TODO: Adapt to work without store code https://github.com/magento/catalog-storefront/issues/417
                self::EMPTY_STORE_CODE => [
                    CatalogRepository::DELETE_BY_QUERY => $deleteFields
                ]
            ]
        ];

        $deleteVariantsResponse = $this->deleteVariantsResponseFactory->create();
        try {
            $this->catalogRepository->saveToStorage($variantsInElasticFormat);
            $deleteVariantsResponse->setMessage('Product variants were removed successfully');
            $deleteVariantsResponse->setStatus(true);
        } catch (\Throwable $e) {
            $message = 'Unable to delete product variants';
            $this->logger->error($message, ['exception' => $e]);
            $deleteVariantsResponse->setMessage($message);
            $deleteVariantsResponse->setStatus(false);
        }

        return $deleteVariantsResponse;
    }

    /**
     * Get product variants from storage.
     *
     * Only variants whose corresponding products are 'enabled' and stored in storage are returned.
     * TODO: Add pagination https://github.com/magento/catalog-storefront/issues/418
     *
     * @param ProductVariantRequestInterface $request
     * @return ProductVariantResponseInterface
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function GetProductVariants(ProductVariantRequestInterface $request): ProductVariantResponseInterface
    {
        $productId = $request->getProductId();
        $store = $request->getStore();
        $rawVariants = $this->productVariantsDataProvider->fetchByProductId((int)$productId);

        if (empty($rawVariants)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No products variants for product with id %s are found in catalog.',
                    $productId
                )
            );
        }

        $compositeVariants = [];
        foreach ($rawVariants as $rawVariant) {
            $compositeVariants[$rawVariant['id']]['id'] = $rawVariant['id'];
            $compositeVariants[$rawVariant['id']]['option_values'][] = $rawVariant['option_value'];
            $compositeVariants[$rawVariant['id']]['product_id'] = $rawVariant['product_id'];
        }

        $productsGetRequest = $this->productsGetRequestInterfaceFactory->create();
        $productsGetRequest->setIds(\array_column($compositeVariants, 'product_id'));
        $productsGetRequest->setStore($store);
        $productsGetRequest->setAttributeCodes(["id", "status"]);
        $catalogProducts = $this->catalogService->getProducts($productsGetRequest)->getItems();

        $activeProducts = [];
        foreach ($catalogProducts as $product) {
            if ($product->getStatus() === self::PRODUCT_STATUS_ENABLED) {
                $activeProducts[$product->getId()] = $product->getId();
            }
        }

        $variants = [];
        foreach ($compositeVariants as $compositeVariant) {
            if (isset($activeProducts[$compositeVariant['product_id']])) {
                $variants[] = $this->productVariantMapper->setData($compositeVariant)->build();
            }
        }

        if (empty($variants)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No valid products variants for product with id %s are found in catalog.',
                    $productId
                )
            );
        }

        $response = new ProductVariantResponse();
        $response->setMatchedVariants($variants);
        return $response;
    }

    /**
     * TODO: Implement GetVariantsMatch() method and remove the warning suppression.
     *
     * @param OptionSelectionRequestInterface $request
     * @return ProductVariantResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function GetVariantsMatch(OptionSelectionRequestInterface $request): ProductVariantResponseInterface
    {
        return new ProductVariantResponse();
    }
}
