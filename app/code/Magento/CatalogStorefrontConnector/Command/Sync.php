<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Command;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogStorefrontConnector\Model\Publisher\ProductPublisher;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
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
    private const INPUT_BATCH_SIZE = 'batch_size';

    /**
     * Default batch size
     * @var int
     */
    private const DEFAULT_BATCH_SIZE = 1000;

    /**
     * @var ProductPublisher
     */
    private $productPublisher;

    /**
     * @var CollectionFactory
     */
    private $productsCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProductPublisher $productPublisher
     * @param CollectionFactory $productsCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductPublisher $productPublisher,
        CollectionFactory $productsCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct();
        $this->productPublisher = $productPublisher;
        $this->productsCollectionFactory = $productsCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Run full reindex for Catalog Storefront service')
            ->addOption(
                self::INPUT_BATCH_SIZE,
                null,
                InputOption::VALUE_NONE,
                'Batch size of processed amount of entities in one time'
            );

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>' . 'Start product sync' . '</info>');

        // TODO: clean product ids from storefront.catalog.product.update topic
        foreach ($this->storeManager->getStores() as $store) {
            $lastProductId = 0;
            $productCollection = $this->productsCollectionFactory->create();
            $productCollection->addWebsiteFilter($store->getWebsiteId());

            while ($productIds = $productCollection->getAllIds($this->getBatchSize($input), $lastProductId)) {
                $lastProductId = \end($productIds);
                $this->productPublisher->publish($productIds, (int)$store->getId());
            }
        }
    }

    /**
     * Get batch size
     *
     * @param InputInterface $input
     * @return int
     */
    private function getBatchSize(InputInterface $input): int
    {
        return (int) $input->getOption(self::INPUT_BATCH_SIZE) ?: self::DEFAULT_BATCH_SIZE;
    }
}
