<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogStorefront\Model\MessageBus;

use Magento\CatalogStorefront\Model\Storage\Client\CommandInterface;
use Magento\CatalogStorefront\Model\Storage\Client\DataDefinitionInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\CatalogExtractor\DataProvider\DataProviderInterface;
use Magento\CatalogStorefront\Model\ProductRetrieverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class NewConsumer extends Consumer
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var ProductRetrieverInterface
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
     * @param ProductRetrieverInterface $productRetriever
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CommandInterface $storageWriteSource,
        DataDefinitionInterface $storageSchemaManager,
        State $storageState,
        CatalogItemMessageBuilder $catalogItemMessageBuilder,
        LoggerInterface $logger,
        DataProviderInterface $dataProvider,
        ProductRetrieverInterface $productRetriever,
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
        $overrides = $this->productRetriever->retrieve($ids);
        foreach ($overrides as $override) {
            $store = $this->storeManager->getStores(false, $override['store_view_code']);
            // @todo check if store exists
            $storeId = array_pop($store)->getId();
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
                'status' => $override['status'],
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
