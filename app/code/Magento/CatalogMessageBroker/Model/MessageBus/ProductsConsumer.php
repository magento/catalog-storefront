<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogExport\Model\Data\ChangedEntitiesDataInterface;
use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\DeleteProductsRequestInterfaceFactory;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Process product update messages and update storefront app
 */
class ProductsConsumer
{
    /**
     * todo: move these constants
     * Event types
     */
    const PRODUCTS_UPDATED_EVENT_TYPE = 'products_updated';

    const PRODUCTS_DELETED_EVENT_TYPE = 'products_deleted';

    /**
     * @var FetchProductsInterface
     */
    private $fetchProducts;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductPublisher
     */
    private $productPublisher;

    /**
     * @var CatalogServerInterface
     */
    private $catalogServer;

    /**
     * @var DeleteProductsRequestInterfaceFactory
     */
    private $deleteProductsRequestInterfaceFactory;

    /**
     * @param LoggerInterface $logger
     * @param FetchProductsInterface $fetchProducts
     * @param StoreManagerInterface $storeManager
     * @param CatalogServerInterface $catalogServer
     * @param DeleteProductsRequestInterfaceFactory $deleteProductsRequestInterfaceFactory
     * @param ProductPublisher $productPublisher
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LoggerInterface $logger,
        FetchProductsInterface $fetchProducts,
        StoreManagerInterface $storeManager,
        CatalogServerInterface $catalogServer,
        DeleteProductsRequestInterfaceFactory $deleteProductsRequestInterfaceFactory,
        ProductPublisher $productPublisher
    ) {
        $this->logger = $logger;
        $this->fetchProducts = $fetchProducts;
        $this->storeManager = $storeManager;
        $this->productPublisher = $productPublisher;
        $this->catalogServer = $catalogServer;
        $this->deleteProductsRequestInterfaceFactory = $deleteProductsRequestInterfaceFactory;
    }

    /**
     * Process message
     *
     * @param ChangedEntitiesDataInterface $message
     * @return void
     */
    public function processMessage(ChangedEntitiesDataInterface $message): void
    {
        try {
            // @todo eliminate store manager
            $storesToIds = $this->getMappedStores();

            if ($message->getEventType() === self::PRODUCTS_UPDATED_EVENT_TYPE) {
                $productsData = $this->fetchProducts->getByIds($message->getEntityIds());
                if (!empty($productsData)) {
                    $productsPerStore = [];
                    foreach ($productsData as $productData) {
                        $dataStoreId = $this->resolveStoreId($storesToIds, $productData['store_view_code']);
                        $productsPerStore[$dataStoreId][$productData['product_id']] = $productData;
                    }
                    foreach ($productsPerStore as $storeId => $products) {
                        $this->publishProducts($products, $storeId);
                    }
                }
                // @todo temporary solution. Deleted products must be processed from different message in queue
                // message must be published into \Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer::process
            } elseif ($message->getEventType() === self::PRODUCTS_DELETED_EVENT_TYPE) {
                $this->deleteProducts($message->getEntityIds(), $message->getScope());
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Publishes products to storage
     *
     * @param array $products
     * @param int $storeId
     */
    private function publishProducts(array $products, int $storeId)
    {
        try {
            $this->productPublisher->publish(\array_keys($products), $storeId, $products);
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while publishing products: "%s"', $e));
        }
    }

    /**
     * Delete products from storage
     *
     * @param string $storeId
     * @param int[] $productIds
     * @return void
     */
    private function deleteProducts(array $productIds, string $storeId): void
    {
        $deleteProductRequest = $this->deleteProductsRequestInterfaceFactory->create();
        $deleteProductRequest->setProductIds($productIds);
        $deleteProductRequest->setStore($storeId);

        try {
            $importResult = $this->catalogServer->deleteProducts($deleteProductRequest);
            if ($importResult->getStatus() === false) {
                $this->logger->error(sprintf('Products deletion has failed: "%s"', $importResult->getMessage()));
            }
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while deleting products: "%s"', $e));
        }
    }

    /**
     * Retrieve mapped stores, in case if something went wrong, retrieve just one default store
     *
     * @return array
     */
    private function getMappedStores(): array
    {
        try {
            // @todo eliminate store manager
            $stores = $this->storeManager->getStores(true);
            $storesToIds = [];
            foreach ($stores as $store) {
                $storesToIds[$store->getCode()] = $store->getId();
            }
        } catch (\Throwable $e) {
            $storesToIds['default'] = 1;
        }

        return $storesToIds;
    }

    /**
     * Resolve store ID by store code
     *
     * @param array $mappedStores
     * @param string $storeCode
     * @return int|mixed
     */
    private function resolveStoreId(array $mappedStores, string $storeCode)
    {
        //workaround for tests
        return $mappedStores[$storeCode] ?? 1;
    }
}
