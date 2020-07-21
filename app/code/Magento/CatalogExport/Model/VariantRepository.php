<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogExport\Model;

use Magento\CatalogExportApi\Api\VariantRepositoryInterface;

/**
 * Default implementation of product variants repository.
 */
class VariantRepository implements VariantRepositoryInterface
{
    /**
     * Constant value for setting max items in response.
     */
    private const MAX_ITEMS_IN_RESPONSE = 250;

    /**
     * Config path of max numbers of variants allowed in response.
     */
    private const CATALOG_EXPORT_MAX_VARIANTS_IN_RESPONSE = 'catalog_export/max_variants_in_response';

    /**
     * @var \Magento\DataExporter\Model\FeedInterface
     */
    private $variantFeed;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \Magento\CatalogExport\Model\Data\VariantFactory
     */
    private $variantFactory;

    /**
     * @param \Magento\DataExporter\Model\FeedInterface $variantFeed
     * @param \Magento\CatalogExport\Model\Data\VariantFactory $variantFactory
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\DataExporter\Model\FeedInterface $variantFeed,
        \Magento\CatalogExport\Model\Data\VariantFactory $variantFactory,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        LoggerInterface $logger
    ) {
        $this->variantFeed = $variantFeed;
        $this->variantFactory = $variantFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function get(array $ids): array
    {
        $this->validateIds($ids);

        $variants = [];
        foreach ($this->fetchData($ids) as $feedItem) {
            $feedItem['id'] = $feedItem['productId'];
            $variants[] = $this->variantFactory->create($feedItem);
        }

        return $variants;
    }

    /**
     * Get max items in response.
     *
     * @return int
     */
    private function getMaxItemsInResponse()
    {
        $maxItemsInResponse = (int) $this->deploymentConfig->get(self::CATALOG_EXPORT_MAX_VARIANTS_IN_RESPONSE);
        return $maxItemsInResponse ?: self::MAX_ITEMS_IN_RESPONSE;
    }

    /**
     * Validate ids input array.
     *
     * @param string[] $ids
     * @throws \InvalidArgumentException
     */
    private function validateIds(array $ids): void
    {
        if (count($ids) > $this->getMaxItemsInResponse()) {
            throw new \InvalidArgumentException(
                'Max items in the response can\'t exceed '
                . $this->getMaxItemsInResponse()
                . '.'
            );
        }
    }

    /**
     * Retrieve list of variants' data from Variant feed by ids.
     *
     * @param string[] $ids
     * @return array
     */
    private function fetchData(array $ids): array
    {
        $feedData = $this->variantFeed->getFeedByIds($ids);
        if (empty($feedData['feed'])) {
            $this->logger->error(\sprintf('Cannot find Variants in feed with ids "%s"', $ids));
        }

        return $feedData['feed'];
    }
}
