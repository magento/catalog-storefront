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
     * TODO: Adapt to work without store code
     * https://github.com/magento/catalog-storefront/issues/417 and remove this constant
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
    public function importProductVariants(ImportVariantsRequestInterface $request): ImportVariantsResponseInterface
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
    public function deleteProductVariants(DeleteVariantsRequestInterface $request): DeleteVariantsResponseInterface
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
    public function getProductVariants(ProductVariantRequestInterface $request): ProductVariantResponseInterface
    {
        $productId = $request->getProductId();
        $store = $request->getStore();
        $variantData = $this->productVariantsDataProvider->fetchByParentIds([(int)$productId]);

        \ksort($variantData);
        if (empty($variantData)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No products variants for product with id %s are found in catalog.',
                    $productId
                )
            );
        }

        $variants = $this->formatVariants($variantData);
        $validVariants = $this->validateVariants($variants, $store);

        if (empty($validVariants)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No valid products variants for product with id %s are found in catalog.',
                    $productId
                )
            );
        }

        $output = [];
        foreach ($validVariants as $variant) {
            $output[] = $this->productVariantMapper->setData($variant)->build();
        }

        $response = new ProductVariantResponse();
        $response->setMatchedVariants($output);
        return $response;
    }

    /**
     * Match the variants which correspond, and do not contradict, the merchant selection.
     *
     * @param OptionSelectionRequestInterface $request
     * @return ProductVariantResponseInterface
     * @throws RuntimeException
     * @throws \Throwable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVariantsMatch(OptionSelectionRequestInterface $request): ProductVariantResponseInterface
    {
        $values = $request->getValues();
        $store = $request->getStore();
        $variantIds = $this->productVariantsDataProvider->fetchVariantIdsByOptionValues($values);
        $variantData = empty($variantIds) ? [] : $this->productVariantsDataProvider->fetchByVariantIds($variantIds);

        $validVariants = [];
        if (!empty($variantData)) {
            \ksort($variantData);
            $variants = $this->formatVariants($variantData);
            $validVariants = $this->validateVariants($variants, $store);
        }

        $output = [];
        foreach ($validVariants as $variant) {
            $output[] = $this->productVariantMapper->setData($variant)->build();
        }

        $response = new ProductVariantResponse();
        $response->setMatchedVariants($output);
        return $response;
    }

    /**
     * Match full variant which matches the merchant selection exactly
     *
     * @param OptionSelectionRequestInterface $request
     * @return ProductVariantResponseInterface
     * @throws RuntimeException
     * @throws \Throwable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVariantsExactlyMatch(OptionSelectionRequestInterface $request): ProductVariantResponseInterface
    {
        $values = $request->getValues();
        $store = $request->getStore();
        $variantIds = $this->productVariantsDataProvider->fetchVariantIdsByOptionValues($values);
        $variantData = count($variantIds) !== 1 ?
            [] :
            $this->productVariantsDataProvider->fetchByVariantIds($variantIds);

        $validVariants = [];
        if (!empty($variantData) && count($variantData) === count($values)) {
            \ksort($variantData);
            $variants = $this->formatVariants($variantData);
            $validVariants = $this->validateVariants($variants, $store);
        }

        $output = [];
        foreach ($validVariants as $variant) {
            $output[] = $this->productVariantMapper->setData($variant)->build();
        }

        $response = new ProductVariantResponse();
        $response->setMatchedVariants($output);
        return $response;
    }

    /**
     * Get all variants which contain at least one of merchant selections
     *
     * @param OptionSelectionRequestInterface $request
     * @return ProductVariantResponseInterface
     * @throws RuntimeException
     * @throws \Throwable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVariantsInclude(OptionSelectionRequestInterface $request): ProductVariantResponseInterface
    {
        $values = $request->getValues();
        $store = $request->getStore();
        $variantIds = $this->productVariantsDataProvider->fetchVariantIdsByOptionValues($values, false);
        $variantData = empty($variantIds) ? [] : $this->productVariantsDataProvider->fetchByVariantIds($variantIds);

        $validVariants = [];
        if (!empty($variantData)) {
            \ksort($variantData);
            $variants = $this->formatVariants($variantData);
            $validVariants = $this->validateVariants($variants, $store);
        }

        $output = [];
        foreach ($validVariants as $variant) {
            $output[] = $this->productVariantMapper->setData($variant)->build();
        }

        $response = new ProductVariantResponse();
        $response->setMatchedVariants($output);
        return $response;
    }

    /**
     * Combine option values into product variants
     *
     * @param array $optionValueData
     * @return array
     */
    private function formatVariants(array $optionValueData): array
    {
        $variants = [];
        foreach ($optionValueData as $optionValue) {
            $variants[$optionValue['id']]['id'] = $optionValue['id'];
            $variants[$optionValue['id']]['option_values'][] = $optionValue['option_value'];
            $variants[$optionValue['id']]['product_id'] = $optionValue['product_id'];
        }
        return $variants;
    }

    /**
     * Validate that the variant products exist and are enabled. Unset invalid variants.
     *
     * @param array $variants
     * @param string $store
     * @return array
     * @throws \Throwable
     */
    private function validateVariants(array $variants, string $store): array
    {
        $productsGetRequest = $this->productsGetRequestInterfaceFactory->create();
        $productsGetRequest->setIds(\array_column($variants, 'product_id'));
        $productsGetRequest->setStore($store);
        $productsGetRequest->setAttributeCodes(["id", "status"]);
        $catalogProducts = $this->catalogService->getProducts($productsGetRequest)->getItems();

        $activeProductIds = [];
        foreach ($catalogProducts as $product) {
            if ($product->getStatus() === self::PRODUCT_STATUS_ENABLED) {
                $activeProductIds[$product->getId()] = $product->getId();
            }
        }

        foreach ($variants as $key => $compositeVariant) {
            if (!isset($activeProductIds[$compositeVariant['product_id']])) {
                unset($variants[$key]);
            }
        }

        return $variants;
    }
}
