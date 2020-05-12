<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogMessageBroker\Model\MessageBus;

use Magento\CatalogStorefront\Model\Storage\Client\CommandInterface;
use Magento\CatalogStorefront\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogStorefront\Model\MessageBus\Consumer as OldConsumer;
use Magento\CatalogStorefront\Model\MessageBus\CatalogItemMessageBuilder;
use Psr\Log\LoggerInterface;

class Consumer extends OldConsumer
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var FetchProductsInterface
     */
    private $productRetriever;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CommandInterface $storageWriteSource
     * @param DataDefinitionInterface $storageSchemaManager
     * @param State $storageState
     * @param CatalogItemMessageBuilder $catalogItemMessageBuilder
     * @param LoggerInterface $logger
     * @param DataProviderInterface $dataProvider
     * @param FetchProductsInterface $productRetriever
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CommandInterface $storageWriteSource,
        DataDefinitionInterface $storageSchemaManager,
        State $storageState,
        CatalogItemMessageBuilder $catalogItemMessageBuilder,
        LoggerInterface $logger,
        DataProviderInterface $dataProvider,
        FetchProductsInterface $productRetriever,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $storageWriteSource,
            $storageSchemaManager,
            $storageState,
            $catalogItemMessageBuilder,
            $logger
        );
        $this->logger = $logger;
        $this->dataProvider = $dataProvider;
        $this->productRetriever = $productRetriever;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string[] $ids
     */
    public function processMessage(array $ids)
    {
        $dataPerType = [];
        $overrides = $this->productRetriever->execute($ids);
        foreach ($overrides as $override) {
            // @todo eliminate store manager
            $store = $this->storeManager->getStores(false, $override['store_view_code']);
            $storeId = array_pop($store)->getId();
            // @todo eliminate calling old API when new API can provide all of the necessary data
            $products = $this->dataProvider->fetch([$override['id']], [], ['store' => $storeId]);
            $product = $this->mergeData(array_pop($products), $override);
            if (empty($product)) {
                $dataPerType['product'][$storeId][self::DELETE][] = $product['entity_id'];
            } else {
                $product['store_id'] = $storeId;
                $dataPerType['product'][$storeId][self::SAVE][] = $product;
            }
        }
        try {
            $this->saveToStorage($dataPerType);
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param array $product
     * @param array $override
     * @return array
     */
    private function mergeData($product, $override)
    {
        return array_merge(
            $product,
            [
                'id' => $override['id'],
                'sku' => $override['sku'],
                'name' => $override['name'],
                'meta_description' => $override['meta_description'],
                'meta_keyword' => $override['meta_keyword'],
                'meta_title' => $override['meta_title'],
                //'status' => $override['status'],
                'tax_class_id' => $override['tax_class_id'],
                'created_at' => $override['created_at'],
                'updated_at' => $override['updated_at'],
                'url_key' => $override['url_key'],
                'visibility' => $override['visibility'],
                'weight' => $override['weight'],
                //'categories' => $override['categories'],
                //'options' => $override['options'],
            ]
        );
    }
}
