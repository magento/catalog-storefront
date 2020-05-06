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
     * @param CommandInterface $storageWriteSource
     * @param DataDefinitionInterface $storageSchemaManager
     * @param State $storageState
     * @param CatalogItemMessageBuilder $catalogItemMessageBuilder
     * @param LoggerInterface $logger
     * @param DataProviderInterface $dataProvider
     * @param ProductRetrieverInterface $productRetriever
     */
    public function __construct(
        CommandInterface $storageWriteSource,
        DataDefinitionInterface $storageSchemaManager,
        State $storageState,
        CatalogItemMessageBuilder $catalogItemMessageBuilder,
        LoggerInterface $logger,
        DataProviderInterface $dataProvider,
        ProductRetrieverInterface $productRetriever
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
    }

    /**
     * @param string[] $ids
     */
    public function processMessage(array $ids)
    {
        $dataPerType = [];
        $products = $this->dataProvider->fetch($ids, [], ['store' => 1]);
        $productsToOverride = $this->productRetriever->retrieve($ids);
        $products = $this->mergeProductData($products, $productsToOverride);
        foreach ($products as $product) {
            if (empty($product)) {
                $dataPerType['product'][1][self::DELETE][] = $product['entity_id'];
            } else {
                $dataPerType['product'][1][self::SAVE][] = $product;
            }
        }
        try {
            $this->saveToStorage($dataPerType);
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param array $products
     * @param array $productsToOverride
     * @return array
     */
    private function mergeProductData($products, $productsToOverride)
    {
        $data = [];
        foreach ($products as $key => $product) {
            $productToOverride = $this->findProductById($productsToOverride, $product['entity_id']);
            if (!empty($productToOverride)) {
                $data[$key] = $products[$key];
                foreach ($productToOverride as $productToOverrideKey => $productToOverrideValue) {
                    if (array_key_exists($productToOverrideKey, $products[$key])) {
                        $data[$key][$productToOverrideKey] = $productToOverrideValue;
                    }
                }
                $data[$key]['store_id'] = 1;
            } else {
                $data[$key] = ['entity_id' => $product['entity_id']];
            }
        }
        return $data;
    }

    /**
     * @param array $productsToOverride
     * @param string $id
     * @return array
     */
    private function findProductById($productsToOverride, $id)
    {
        $data = [];
        foreach ($productsToOverride as $productToOverride) {
            if ($productToOverride['id'] == $id) {
                $data = $productToOverride;
                break;
            }
        }
        return $data;
    }
}
