<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogExport\Model\Data\ChangedEntitiesInterface;
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
     * Event types to handle incoming messages from Export API
     * TODO: make private after https://github.com/magento/catalog-storefront/issues/242
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
        $this->productPublisher = $productPublisher;
        $this->catalogServer = $catalogServer;
        $this->deleteProductsRequestInterfaceFactory = $deleteProductsRequestInterfaceFactory;
    }

    /**
     * Process message
     *
     * @param \Magento\CatalogExport\Model\Data\ChangedEntitiesInterface $message
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processMessage(ChangedEntitiesInterface $message): void
    {
        try {
            $eventType = $message->getMeta() ? $message->getMeta()->getEventType() : null;
            $scope = $message->getMeta() ? $message->getMeta()->getScope() : null;
            $entityIds = $message->getData() ? $message->getData()->getIds() : null;

            if (empty($entityIds)) {
                throw new \InvalidArgumentException('Product ids are missing in payload');
            }

            if ($eventType === self::PRODUCTS_UPDATED_EVENT_TYPE) {
                $productsData = $this->fetchProducts->getByIds(
                    $entityIds,
                    array_filter([$scope])
                );
                if (!empty($productsData)) {
                    $productsPerStore = [];
                    foreach ($productsData as $productData) {
                        $productsPerStore[$productData['store_view_code']][$productData['product_id']] = $productData;
                    }
                    foreach ($productsPerStore as $storeCode => $products) {
                        $this->publishProducts($products, $storeCode);
                    }
                }
                // message must be published into \Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer::process
            } elseif ($eventType === self::PRODUCTS_DELETED_EVENT_TYPE) {
                $this->deleteProducts($entityIds, $scope);
            } else {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'The provided event type "%s" was not recognized',
                        $eventType
                    )
                );
            }
        } catch (\Throwable $e) {
            $this->logger->critical('Unable to process collected product data for update/delete. ' . $e->getMessage());
        }
    }

    /**
     * Publishes products to storage
     *
     * @param array $products
     * @param string $storeCode
     */
    private function publishProducts(array $products, string $storeCode)
    {
        try {
            $this->productPublisher->publish(\array_keys($products), $storeCode, $products);
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while publishing products: "%s"', $e));
        }
    }

    /**
     * Delete products from storage
     *
     * @param int[] $productIds
     * @param string $storeCode
     * @return void
     */
    private function deleteProducts(array $productIds, string $storeCode): void
    {
        $deleteProductRequest = $this->deleteProductsRequestInterfaceFactory->create();
        $deleteProductRequest->setProductIds($productIds);
        $deleteProductRequest->setStore($storeCode);

        try {
            $importResult = $this->catalogServer->deleteProducts($deleteProductRequest);
            if ($importResult->getStatus() === false) {
                $this->logger->error(sprintf('Products deletion has failed: "%s"', $importResult->getMessage()));
            }
        } catch (\Throwable $e) {
            $this->logger->critical(sprintf('Exception while deleting products: "%s"', $e));
        }
    }
}
