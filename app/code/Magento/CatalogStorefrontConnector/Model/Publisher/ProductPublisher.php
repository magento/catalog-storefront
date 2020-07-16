<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;
use Magento\CatalogMessageBroker\Model\ProductDataProcessor;


/**
 * Product publisher
 *
 * Push product data for given product ids and store id to the Storefront via Import API
 * TODO: move to CatalogMessageBroker module
 */
class ProductPublisher
{
    /**
     * @var DataProviderInterface
     */
    private $productsDataProvider;

    /**
     * @var CatalogItemMessageBuilder

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CatalogServerInterface
     */
    private $catalogServer;
    /**
     * @var ImportProductsRequestInterfaceFactory
     */
    private $importProductsRequestInterfaceFactory;
    /**
     * @var ProductDataProcessor
     */
    private $productDataProcessor;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param DataProviderInterface $productsDataProvider
     * @param State $state
     * @param LoggerInterface $logger
     * @param CatalogServerInterface $catalogServer
     * @param ImportProductsRequestInterfaceFactory $importProductsRequestInterfaceFactory
     * @param ProductDataProcessor $productDataProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $productsDataProvider,
        State $state,
        LoggerInterface $logger,
        CatalogServerInterface $catalogServer,
        ImportProductsRequestInterfaceFactory $importProductsRequestInterfaceFactory,
        ProductDataProcessor $productDataProcessor,
        DataObjectHelper $dataObjectHelper,
        int $batchSize
    ) {
        $this->productsDataProvider = $productsDataProvider;
        $this->batchSize = $batchSize;
        $this->state = $state;
        $this->logger = $logger;
        $this->catalogServer = $catalogServer;
        $this->importProductsRequestInterfaceFactory = $importProductsRequestInterfaceFactory;
        $this->productDataProcessor = $productDataProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Publish new messages to storefront.catalog.data.consume topic
     *
     * @param array $productIds
     * @param int $storeId
     * @param array $overrideProducts Temporary variables to support transition period between new and old Export API
     * @return void
     * @throws \Exception
     */
    public function publish(array $productIds, int $storeId, $overrideProducts = []): void
    {

        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            function () use ($productIds, $storeId, $overrideProducts) {
                try {
                    $this->publishEntities($productIds, $storeId, $overrideProducts);
                } catch (\Throwable $e) {
                    $this->logger->critical(
                        \sprintf(
                            'Error on publish product ids "%s" in store %s',
                            \implode(', ', $productIds),
                            $storeId
                        ),
                        ['exception' => $e]
                    );
                }
            }
        );
    }

    /**
     * Publish entities to the queue
     *
     * @param array $productIds
     * @param int $storeId
     * @param array $overrideProducts
     * @return void
     */
    private function publishEntities(array $productIds, int $storeId, $overrideProducts = []): void
    {
        foreach (\array_chunk($productIds, $this->batchSize) as $idsBunch) {
            // @todo eliminate calling old API when new API can provide all of the necessary data
            $productsData = $this->productsDataProvider->fetch($idsBunch, [], ['store' => $storeId]);
            $this->logger->debug(
                \sprintf('Publish products with ids "%s" in store %s', \implode(', ', $productIds), $storeId),
                ['verbose' => $productsData]
            );
            if (count($productsData)) {
                $this->importProducts($storeId, array_values($productsData), $overrideProducts);
            }
        }
    }

    /**
     * Recursively unset array elements equal to NULL.
     *
     * @param array $haystack
     * @return void
     */
    private function unsetNullRecursively(&$haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $this->unsetNullRecursively($haystack[$key]);
            }
            if ($haystack[$key] === null) {
                unset($haystack[$key]);
            }
        }
    }

    /**
     * @param int $storeId
     * @param array $products
     * @param array $overrideProducts
     * @throws \Throwable
     */
    private function importProducts($storeId, array $products, $overrideProducts = []): void
    {
        $newApiProducts = [];
        foreach ($overrideProducts as $product) {
            $newApiProducts[$product['product_id']] = $product;
        }

        $this->unsetNullRecursively($products);
        foreach ($products as &$product) {
            if (isset($newApiProducts[$product['entity_id']])) {
                $product = $this->productDataProcessor->merge($newApiProducts[$product['entity_id']], $product);
            }
            // TODO: add conversion to validate input data
//            $product = $this->dataObjectHelper->populateWithArray(
//                new \Magento\CatalogStorefrontApi\Api\Data\Product,
//                $product,
//                \Magento\CatalogStorefrontApi\Api\Data\ProductInterface::class
//            );
        }
        unset($product);

        $importProductRequest = $this->importProductsRequestInterfaceFactory->create();
        $importProductRequest->setProducts($products);
        $importProductRequest->setStore($storeId);
        $importResult = $this->catalogServer->importProducts($importProductRequest);
        if ($importResult->getStatus() === false) {
            $this->logger->error(sprintf('Products import is failed: "%s"', $importResult->getMessage()));
        }
    }
}
