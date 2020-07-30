<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Command;

use Magento\CatalogMessageBroker\Model\FetchProductsInterface;
use Magento\CatalogDataExporter\Model\Indexer\CategoryFeedIndexer;
use Magento\CatalogMessageBroker\Model\MessageBus\CategoriesConsumer;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sync Catalog data with Storefront storage. Collect product data and push it to the Message Bus
 */
class Sync extends Command
{
    /**
     * Command name
     * @var string
     */
    private const COMMAND_NAME = 'storefront:catalog:sync';

    /**
     * Option name for batch size
     * @var string
     */
    private const INPUT_ENTITY_TYPE = 'entity';

    /**
     * Product entity type
     */
    private const ENTITY_TYPE_PRODUCT = 'product';

    /**
     * Category entity type
     */
    private const ENTITY_TYPE_CATEGORY = 'category';

    /**
     * @var ProductPublisher
     */
    private $productPublisher;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CatalogEntityIdsProvider
     */
    private $catalogEntityIdsProvider;

    /**
     * @var ProductFeedIndexer
     */
    private $productFeedIndexer;

    /**
     * @var CategoriesConsumer
     */
    private $categoriesConsumer;
    /**
     * @var CategoryFeedIndexer
     */
    private $categoryFeedIndexer;

    /**
     * @var FetchProductsInterface
     */
    private $fetchProducts;

    /**
     * @param ProductPublisher $productPublisher
     * @param StoreManagerInterface $storeManager
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     * @param CategoriesConsumer $categoriesConsumer
     * @param ProductFeedIndexer $productFeedIndexer
     * @param CategoryFeedIndexer $categoryFeedIndexer
     * @param FetchProductsInterface $fetchProducts
     */
    public function __construct(
        ProductPublisher $productPublisher,
        StoreManagerInterface $storeManager,
        CatalogEntityIdsProvider $catalogEntityIdsProvider,
        CategoriesConsumer $categoriesConsumer,
        ProductFeedIndexer $productFeedIndexer,
        CategoryFeedIndexer $categoryFeedIndexer,
        \Magento\CatalogMessageBroker\Model\FetchProductsInterface $fetchProducts
    ) {
        parent::__construct();
        $this->productPublisher = $productPublisher;
        $this->storeManager = $storeManager;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->productFeedIndexer = $productFeedIndexer;
        $this->categoriesConsumer = $categoriesConsumer;
        $this->categoryFeedIndexer = $categoryFeedIndexer;
        $this->fetchProducts = $fetchProducts;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Run full reindex for Catalog Storefront service')
            ->addOption(
                self::INPUT_ENTITY_TYPE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Entity type code for process. Possible values: product, category. '
                . 'By default all entities will be processed'
            );

        parent::configure();
    }

    /**
     * Sync between Magento and storefront
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Magento\DataExporter\Exception\UnableRetrieveData
     * @throws \Zend_Db_Statement_Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityType = $this->getEntityType($input);
        // TODO: MC-30961 clean product ids from storefront.catalog.category.update topic

        // @todo eliminate dependency on indexer
        if (!$entityType || $entityType === self::ENTITY_TYPE_CATEGORY) {
            $this->categoryFeedIndexer->executeFull();
        }
        if (!$entityType || $entityType === self::ENTITY_TYPE_PRODUCT) {
            $this->productFeedIndexer->executeFull();
        }

        foreach ($this->storeManager->getStores() as $store) {
            $storeId = (int)$store->getId();

            if (!$entityType || $entityType === self::ENTITY_TYPE_PRODUCT) {
                $this->syncProducts($output, $storeId);
            }
            if (!$entityType || $entityType === self::ENTITY_TYPE_CATEGORY) {
                $this->syncCategories($output, $storeId);
            }
        }
    }

    /**
     * Sync products
     *
     * @param OutputInterface $output
     * @param int $storeId
     */
    protected function syncProducts(OutputInterface $output, int $storeId): void
    {
        $output->writeln("<info>Sync products for store {$storeId}</info>");
        $this->measure(
            function () use ($output, $storeId) {
                $processedN = 0;
                foreach ($this->catalogEntityIdsProvider->getProductIds($storeId) as $productIds) {
                    $newApiProducts = $this->fetchProducts->execute($productIds);
                    $this->productPublisher->publish($productIds, $storeId, $newApiProducts);
                    $output->write('.');
                    $processedN += count($productIds);
                }
                return $processedN;
            },
            $output
        );
    }

    /**
     * Sync categories
     *
     * @param OutputInterface $output
     * @param int $storeId
     */
    protected function syncCategories(OutputInterface $output, int $storeId): void
    {
        $output->writeln("<info>Sync categories for store {$storeId}</info>");
        $this->measure(
            function () use ($output, $storeId) {
                $processedN = 0;
                foreach ($this->catalogEntityIdsProvider->getCategoryIds($storeId) as $categoryIds) {
                    $this->categoriesConsumer->processMessage(json_encode($categoryIds));
                    $output->write('.');
                    $processedN += count($categoryIds);
                }
                return $processedN;
            },
            $output
        );
    }

    /**
     * Measure sync time
     *
     * @param callable $func
     * @param OutputInterface $output
     * @return void
     */
    private function measure(callable $func, OutputInterface $output): void
    {
        $start = \time();
        $processedN = $func();
        $output->writeln(
            \sprintf('Complete "%s" entities in "%s"', $processedN, Helper::formatTime(\time() - $start))
        );
    }

    /**
     * Get processed entity type
     *
     * @param InputInterface $input
     * @return string|null
     */
    public function getEntityType(InputInterface $input): ?string
    {
        return $input->getOption(self::INPUT_ENTITY_TYPE) ?: null;
    }
}
