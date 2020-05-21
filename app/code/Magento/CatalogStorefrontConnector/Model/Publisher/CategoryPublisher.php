<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model\Publisher;

use Magento\CategoryExtractor\DataProvider\DataProviderInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;
use Magento\CatalogStorefrontConnector\Model\Publisher\RestClient;

/**
 * Category publisher
 *
 * Push product data for given category ids and store id to the Message Bus
 * with topic storefront.catalog.data.consume
 */
class CategoryPublisher
{
    /**
     * @var DataProviderInterface
     */
    private $categoriesDataProvider;

    /**
     * @var CatalogItemMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var PublisherInterface
     */
    private $queuePublisher;

    /**
     * @var string
     */
    private const TOPIC_NAME = 'storefront.catalog.data.consume';

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
     * @var RestClient
     */
    private $restClient;

    /**
     * @param DataProviderInterface $categoriesDataProvider
     * @param CatalogItemMessageBuilder $messageBuilder
     * @param PublisherInterface $queuePublisher
     * @param State $state
     * @param LoggerInterface $logger
     * @param RestClient $restClient,
     * @param int $batchSize
     */
    public function __construct(
        DataProviderInterface $categoriesDataProvider,
        CatalogItemMessageBuilder $messageBuilder,
        PublisherInterface $queuePublisher,
        State $state,
        LoggerInterface $logger,
        RestClient $restClient,
        int $batchSize
    ) {
        $this->categoriesDataProvider = $categoriesDataProvider;
        $this->messageBuilder = $messageBuilder;
        $this->queuePublisher = $queuePublisher;
        $this->batchSize = $batchSize;
        $this->state = $state;
        $this->logger = $logger;
        $this->restClient = $restClient;
    }

    /**
     * Publish new messages to storefront.catalog.data.consume topic
     *
     * @param array $categoryIds
     * @param int $storeId
     * @return void
     * @throws \Exception
     */
    public function publish(array $categoryIds, int $storeId): void
    {
        $this->state->emulateAreaCode(
            Area::AREA_FRONTEND,
            function () use ($categoryIds, $storeId) {
                try {
                    $this->publishEntities($categoryIds, $storeId);
                } catch (\Throwable $e) {
                    $this->logger->critical(
                        \sprintf(
                            'Error on publish category ids "%s" in store %s',
                            \implode(', ', $categoryIds),
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
     * @param array $categoryIds
     * @param int $storeId
     * @return void
     */
    private function publishEntities(array $categoryIds, int $storeId): void
    {
        foreach (\array_chunk($categoryIds, $this->batchSize) as $idsBunch) {
            $categoriesData = $this->categoriesDataProvider->fetch($idsBunch, [], ['store' => $storeId]);
            $this->unsetNullRecursively($categoriesData);
            $this->logger->debug(
                \sprintf('Publish category with ids "%s" in store %s', \implode(', ', $categoryIds), $storeId),
                ['verbose' => $categoriesData]
            );
            if (count($categoriesData)) {
                $this->importCategories($storeId, array_values($categoriesData));
            }
        }
    }

    /**
     * Recursively unset array elements equal to NULL.
     *
     * TODO: Eliminate duplicate @see \Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher::unsetNullRecursively
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
            'categories',
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
     * @param array $categories
     */
    private function importCategories($storeId, array $categories): void
    {
        foreach ($categories as &$category) {
//            $this->temporaryProductTransformation($category);
        }

        try {
            $this->restClient->post(
                '/V1/storefront-categories',
                ['request' => ['categories' => $categories, 'store' => $storeId]],
                ["Content-Type: application/json"]
            );
        } catch (\Exception $e) {
            // TODO: Implement logging
            die($e->getMessage());
        }
    }
}
