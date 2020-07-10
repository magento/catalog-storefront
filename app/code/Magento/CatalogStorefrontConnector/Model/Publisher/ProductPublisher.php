<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\CatalogStorefrontApi\Api\CatalogServerInterface;
use Magento\CatalogStorefrontApi\Api\Data\ImportProductsRequestInterfaceFactory;
use Magento\Framework\App\State;
use Magento\Framework\MessageQueue\PublisherInterface;
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
     */
    private $messageBuilder;

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

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
     * @param DataProviderInterface $productsDataProvider
     * @param CatalogItemMessageBuilder $messageBuilder
     * @param PublisherInterface $queuePublisher
     * @param State $state
     * @param LoggerInterface $logger
     * @param RestClient $restClient
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $productsDataProvider,
        CatalogItemMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        State $state,
        LoggerInterface $logger,
        RestClient $restClient,
        CatalogServerInterface $catalogServer,
        ImportProductsRequestInterfaceFactory $importProductsRequestInterfaceFactory,
        ProductDataProcessor $productDataProcessor,
        int $batchSize
    ) {
        $this->productsDataProvider = $productsDataProvider;
        $this->messageBuilder = $messageBuilder;
        $this->queuePublisher = $queuePublisher;
        $this->batchSize = $batchSize;
        $this->state = $state;
        $this->logger = $logger;
        $this->restClient = $restClient;
        $this->catalogServer = $catalogServer;
        $this->importProductsRequestInterfaceFactory = $importProductsRequestInterfaceFactory;
        $this->productDataProcessor = $productDataProcessor;
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
     * TODO: this method is temporary. We should adjust what data is imported after import APIs are finalized
     *
     * @param int $storeId
     * @param array $product
     */
    private function temporaryProductTransformation(array &$product): void
    {
        // TODO: This array needs to be reviewed. Temporary, for prototyping purposes
        $unnecessaryAttributeNames = [
            'entity_id',
            'row_id',
            'store_id',
            'swatch_image'
        ];

        $nonCustomAttribtues = [
            'attribute_set_id',
            'has_options',
            'id',
            'type_id',
            'sku',
            'id',
            'status',
            'stock_status',
            'name',
            'description',
            'short_description',
            'visibility',
            'url_key',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'tax_class_id',
            'weight',
            'image',
            'small_image',
            'thumbnail',
            'dynamic_attributes',
            'categories',

            // TODO: Questionable attributes below, needed to preserve backward compatibility with current Catalog SF branch during refactoring
            'required_options',
            'created_at',
            'updated_at',
            'created_in',
            'updated_in',
            'quantity_and_stock_status',
            'options_container',
            'msrp_display_actual_price_type',
            'is_returnable',
            'url_suffix',
            'url_rewrites',
            'variants',
            'options',
            'configurable_options',
        ];
        $product['dynamic_attributes'] = [];
        foreach ($product as $attributeCode => $attributeValue) {
            if (in_array($attributeCode, $unnecessaryAttributeNames)) {
                unset($product[$attributeCode]);
                continue;
            }
            if (!in_array($attributeCode, $nonCustomAttribtues)) {
                $product['dynamic_attributes'][] = ['code' => $attributeCode, 'value' => $attributeValue];
                unset($product[$attributeCode]);
                continue;
            }
        }

        if (isset($product['options']) && is_array($product['options'])) {
            foreach ($product['options'] as &$option) {
                if (isset($option['value'])) {
                    if (isset($option['value']['sku'])) {
                        // TODO: Temporary fix: Option values structure needs to be always an array of objects
                        $option['value'] = [$option['value']];
                    } else {
                        // TODO: Temporary fix: Convert associative array to indexed to make it compatible with REST
                        $option['value'] = array_values($option['value']);
                    }
                }
            }
        }

        $product['short_description'] = $product['short_description'][0]['html'] ?? '';
        $product['description'] = $product['description'][0]['html'] ?? '';
    }

    /**
     * @param int $storeId
     * @param array $products
     * @param array $overrideProducts
     * @throws \Throwable
     */
    private function importProducts($storeId, array $products, $overrideProducts = []): void
    {
        $this->unsetNullRecursively($products);

        foreach ($products as &$product) {
            if (isset($overrideProducts[$product['entity_id']])) {
                $newApiProductData = $overrideProducts[$product['entity_id']];
                $product = $this->productDataProcessor->merge($newApiProductData, $product);
            }

            $this->temporaryProductTransformation($product);
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
