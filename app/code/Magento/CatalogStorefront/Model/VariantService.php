<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Model;

use Magento\CatalogStorefrontApi\Api\Data\DeleteVariantsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteVariantsResponseFactory;
use Magento\CatalogStorefrontApi\Api\Data\DeleteVariantsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportVariantsRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportVariantsResponseFactory;
use Magento\CatalogStorefrontApi\Api\Data\ImportVariantsResponseInterface;
use Magento\CatalogStorefrontApi\Api\Data\OptionSelectionRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantRequestInterface;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantResponseInterface;
use Magento\CatalogStorefrontApi\Api\VariantServiceServerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductVariantArrayMapper
     */
    private $variantArrayMapper;

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param ProductVariantArrayMapper $variantArrayMapper
     * @param ImportVariantsResponseFactory $importVariantsResponseFactory
     * @param DeleteVariantsResponseFactory $deleteVariantsResponseFactory
     * @param LoggerInterface $logger
     * @param CatalogRepository $catalogRepository
     */
    public function __construct(
        ProductVariantArrayMapper $variantArrayMapper,
        ImportVariantsResponseFactory $importVariantsResponseFactory,
        DeleteVariantsResponseFactory $deleteVariantsResponseFactory,
        LoggerInterface $logger,
        CatalogRepository $catalogRepository
    ) {
        $this->variantArrayMapper = $variantArrayMapper;
        $this->importVariantsResponseFactory = $importVariantsResponseFactory;
        $this->logger = $logger;
        $this->catalogRepository = $catalogRepository;
        $this->deleteVariantsResponseFactory = $deleteVariantsResponseFactory;
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
            $storeCode = 'default'; //todo: temporary solution. Remove store code or infer the default somehow.

            $variantsInElasticFormat = [];
            foreach ($request->getVariants() as $variantData) {
                $optionValues = $variantData->getOptionValues();
                $id = $variantData->getId();
                $explodedId = explode('/', $id);
                $parentId = $explodedId[1];
                $childId = $explodedId[2];
                foreach ($optionValues as $optionValue) {
                    preg_match('/(?<=\:)(.*)(?=\/)/', $optionValue, $match);
                    $attrCode = $match[0];
                    $variant = [
                        '_id' => $id . '/' . $attrCode,
                        'id' => $id,
                        'option_value' => $optionValue,
                        'product_id' => $childId,
                        'parent_id' => $parentId
                    ];
                    $variantsInElasticFormat['product_variant'][$storeCode]['save'][] = $variant;
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
        $decodedIds = \array_map(function ($id) {
            return ['id' => $id];
        }, $request->getId());
        $storeId = 'default';

        $variantsInElasticFormat = [
            'product_variant' => [
                $storeId => [
                    'delete_by_query' => $decodedIds
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

    public function GetProductVariants(ProductVariantRequestInterface $request): ProductVariantResponseInterface
    {
        // TODO: Implement GetProductVariants() method.
    }

    public function GetVariantsMatch(OptionSelectionRequestInterface $request): ProductVariantResponseInterface
    {
        // TODO: Implement GetVariantsMatch() method.
    }
}
