<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\Test\Api\Product\Bundle;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogStorefront\Model\CatalogService;
use Magento\CatalogStorefront\Test\Api\StorefrontTestsAbstract;
use Magento\CatalogStorefrontApi\Api\Data\ProductsGetRequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use Magento\CatalogStorefrontApi\Api\Data\BundleItemOptionArrayMapper;
use Magento\CatalogStorefrontApi\Api\Data\ProductVariantsGetRequest;

/**
 * Test Class for Bundle product item options
 */
class BundleProductItemOptionsTest extends StorefrontTestsAbstract
{
    /**
     * Test Constants
     */
    const TEST_SKU = 'bundle-product-dropdown-options';
    const BUNDLE_CHECKBOX_SKU = 'bundle-product-checkbox-options';
    const BUNDLE_DROPDOWN_SKU = 'bundle-product-dropdown-options';
    const STORE_CODE = 'default';

    /**
     * @var string[]
     */
    private $attributesToCompare = [
        'bundle_item_options' //TODO: confirm this attribute for comparision
    ];

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
     * @var CompareArraysRecursively
     */
    private $compareArraysRecursively;

    /**
     * @var BundleItemOptionArrayMapper
     */
    private $arrayMapper;

    /**
     * @var ProductVariantsGetRequest
     */
    private $productVariantInterface;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productsGetRequestInterface = Bootstrap::getObjectManager()->create(ProductsGetRequestInterface::class);
        $this->productVariantInterface = Bootstrap::getObjectManager()->create(ProductVariantsGetRequest::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->catalogService = Bootstrap::getObjectManager()->create(CatalogService::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->arrayMapper = Bootstrap::getObjectManager()->create(BundleItemOptionArrayMapper::class);
        $this->compareArraysRecursively = Bootstrap::getObjectManager()->create(CompareArraysRecursively::class);
    }
    /**
     * Validate configurable product data
     *
     * @magentoDataFixture Magento/Bundle/_files/bundle_product_checkbox_options.php

     * @magentoDbIsolation disabled
     * @param array $checkboxExpectedOptions
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testBundleItemOptions() : void
    {
        $bundleProductCheckboxOptions = $this->productRepository->get(self::BUNDLE_CHECKBOX_SKU);

//        //get all the selection products used in bundle product.
//        $selectionCollection = $product->getTypeInstance(true)
//            ->getSelectionsCollection(
//                $product->getTypeInstance(true)->getOptionsIds($product),
//                $product
//            );
//
//        foreach ($selectionCollection as $proselection) {
//            var_dump($proselection->getData());
//            $selectionArray = [];
//            $selectionArray['selection_product_name'] = $proselection->getName();
//            $selectionArray['selection_product_quantity'] = $proselection->getPrice();
//            $selectionArray['selection_product_price'] = $proselection->getSelectionQty();
//            $selectionArray['selection_product_id'] = $proselection->getProductId();
//            $productsArray[$proselection->getOptionId()][$proselection->getSelectionId()] = $selectionArray;
//        }

//        var_dump($productsArray);

        //get all options of product
//        $optionsCollection = $product->getTypeInstance(true)
//            ->getOptionsCollection($product);

//        foreach ($optionsCollection as $options) {
//            $optionArray[$options->getOptionId()]['option_title'] = $options->getDefaultTitle();
//            $optionArray[$options->getOptionId()]['option_type'] = $options->getType();
//        }
//

        $this->productsGetRequestInterface->setIds([$bundleProductCheckboxOptions->getId()]);
        $this->productsGetRequestInterface->setStore(self::STORE_CODE);
        $this->productsGetRequestInterface->setAttributeCodes($this->attributesToCompare);
        $catalogServiceItem = $this->catalogService->getProducts($this->productsGetRequestInterface);
        $this->assertNotEmpty($catalogServiceItem->getItems());

        var_dump($catalogServiceItem->getItems()[0]->getProductOptions());


        $actualCheckbox = [];
        foreach ($catalogServiceItem->getItems()[0]->getProductOptions() as $productOption) {
            var_dump($productOption);
//            $optionValues = $productOption->getValues();
//            foreach ($optionValues as $productOptionValue) {
//                $convertedOptionValue = $this->arrayMapper->convertToArray($productOptionValue);
//                unset($convertedOptionValue['id']);
//                unset($convertedOptionValue['entity_id']);
//                $actualCheckbox[] = $convertedOptionValue;
//            }
        }

//        var_dump($actualCheckbox);
//        $diffCheckbox = $this->compareArraysRecursively->execute(
//            $checkboxExpectedOptions,
//            $actualCheckbox
//        );
//        self::assertEquals([], $diffCheckbox, "Actual response doesn't equal expected data");
    }

    /**
     * @return array
     */
    public function getCheckboxOptionsProvider() : array
    {
        return [
            [
                [
                    [
//                        'id' => '1', //dynamically created
                        'quantity' => '1.0000',
                        'is_default' =>'0',
                        'price' => '0.0000',
                        'price_type' => '0',
                        'can_change_quantity' => '0',
                        'label' => 'Simple Product',
//                        'entity_id' => '', //dynamically created
                        'position' => '0'
                    ],
                    [
//                        'id' => '1', //dynamically created
                        'quantity' => '',
                        'is_default' =>'0',
                        'price' => '0.0000',
                        'price_type' => '0',
                        'can_change_quantity' => '0',
                        'label' => 'Simple Product2',
//                        'entity_id' => '', //dynamically created
                        'position' => '0'
                    ],
                ]
            ]
        ];
    }

    public function getDropdownOptionsProvider() : array
    {
        return [
            [
                [
                    [
//                        'id' => '1', //dynamically created
                        'quantity' => '1.0000',
                        'is_default' =>'0',
                        'price' => '0.0000',
                        'price_type' => '0',
                        'can_change_quantity' => '0',
                        'label' => 'Simple Product',
//                        'entity_id' => '', //dynamically created
                        'position' => '0'
                    ],
                    [
//                        'id' => '1', //dynamically created
                        'quantity' => '',
                        'is_default' =>'0',
                        'price' => '0.0000',
                        'price_type' => '0',
                        'can_change_quantity' => '0',
                        'label' => 'Simple Product2',
//                        'entity_id' => '', //dynamically created
                        'position' => '0'
                    ],
                ]
            ]
        ];
    }
}
