<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Command;

use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogStorefrontConnector\Model\Publisher\CategoryPublisher;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
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
     * @var CategoryPublisher
     */
    private $categoryPublisher;

    /**
     * @var CatalogEntityIdsProvider
     */
    private $catalogEntityIdsProvider;

    /**
     * @param ProductPublisher $productPublisher
     * @param CategoryPublisher $categoryPublisher
     * @param StoreManagerInterface $storeManager
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     */
    public function __construct(
        ProductPublisher $productPublisher,
        CategoryPublisher $categoryPublisher,
        StoreManagerInterface $storeManager,
        CatalogEntityIdsProvider $catalogEntityIdsProvider
    ) {
        parent::__construct();
        $this->productPublisher = $productPublisher;
        $this->categoryPublisher = $categoryPublisher;
        $this->storeManager = $storeManager;
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
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
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityType = $this->getEntityType($input);
        // TODO: MC-30961 clean product ids from storefront.catalog.category.update topic
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
                    $this->productPublisher->publish('product_updated', $productIds, $storeId);
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
                    $this->categoryPublisher->publish('category_updated', $categoryIds, $storeId);
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
