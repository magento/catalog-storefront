<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefront\DataProvider;

use Magento\CatalogStorefront\Model\Storage\Client\Config\ProductVariant;
use Magento\CatalogStorefront\Model\Storage\Client\QueryInterface;
use Magento\CatalogStorefront\Model\Storage\State;
use Magento\CatalogStorefront\Model\VariantService;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;
use Psr\Log\LoggerInterface;

/**
 * ProductVariant storage reader.
 */
class ProductVariantsDataProvider
{
    /**
     * @var QueryInterface
     */
    private $query;

    /**
     * @var State
     */
    private $storageState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param QueryInterface $query
     * @param State $storageState
     * @param LoggerInterface $logger
     */
    public function __construct(
        QueryInterface $query,
        State $storageState,
        LoggerInterface $logger
    ) {
        $this->query = $query;
        $this->storageState = $storageState;
        $this->logger = $logger;
    }

    /**
     * Fetch product variants data from storage
     *
     * @param int $parentId
     * @return array
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function fetchByProductId(int $parentId): array
    {
        // todo: Adapt to work without store code
        $storageName = $this->storageState->getCurrentDataSourceName(
            [VariantService::EMPTY_STORE_CODE, ProductVariant::ENTITY_NAME]
        );
        try {
            $entities = $this->query->searchFilteredEntries(
                $storageName,
                ProductVariant::ENTITY_NAME,
                ['parent_id' => $parentId]
            );
        } catch (NotFoundException $notFoundException) {
            $this->logger->error(
                \sprintf(
                    'Cannot find product variants for product id "%s"',
                    $parentId,
                ),
                ['exception' => $notFoundException]
            );
            return [];
        } catch (\Throwable $e) {
            $this->logger->error($e);
            throw $e;
        }
        return $entities->toArray(false);
    }
}
