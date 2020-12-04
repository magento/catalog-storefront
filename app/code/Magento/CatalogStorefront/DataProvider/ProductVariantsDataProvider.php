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
     * @param array $parentIds
     * @return array
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function fetchByParentIds(array $parentIds): array
    {
        // TODO: Adapt to work without store code https://github.com/magento/catalog-storefront/issues/417
        $storageName = $this->storageState->getCurrentDataSourceName(
            [VariantService::EMPTY_STORE_CODE, ProductVariant::ENTITY_NAME]
        );
        try {
            $entities = $this->query->searchFilteredEntries(
                $storageName,
                ProductVariant::ENTITY_NAME,
                ['parent_id' => $parentIds]
            );
        } catch (NotFoundException $notFoundException) {
            $this->logger->error(
                \sprintf(
                    'Cannot find product variants for product id "%s"',
                    \implode(",", $parentIds),
                ),
                ['exception' => $notFoundException]
            );
            return [];
        } catch (\Throwable $e) {
            $this->logger->error('Error while trying to fetch product variants by parent ids: ' . $e);
            throw $e;
        }
        return $entities->toArray(false);
    }

    /**
     * Fetch product variants data from storage
     *
     * @param array $variantIds
     * @return array
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function fetchByVariantIds(array $variantIds): array
    {
        // TODO: Adapt to work without store code https://github.com/magento/catalog-storefront/issues/417
        $storageName = $this->storageState->getCurrentDataSourceName(
            [VariantService::EMPTY_STORE_CODE, ProductVariant::ENTITY_NAME]
        );
        try {
            $entities = $this->query->searchFilteredEntries(
                $storageName,
                ProductVariant::ENTITY_NAME,
                ['id' => $variantIds]
            );
        } catch (NotFoundException $notFoundException) {
            $this->logger->error(
                \sprintf(
                    'Cannot find product variants for variant ids "%s"',
                    \implode(",", $variantIds),
                ),
                ['exception' => $notFoundException]
            );
            return [];
        } catch (\Throwable $e) {
            $this->logger->error('Error while trying to fetch product variants by id: ' . $e);
            throw $e;
        }
        return $entities->toArray(false);
    }

    /**
     * Get matching variant ids by option values
     *
     * If strict is true, only variants which contain all of the provided values, will be returned
     *
     * @param array $values
     * @param bool $strict
     * @return array
     * @throws \Throwable
     */
    public function fetchVariantIdsByOptionValues(array $values, bool $strict = true): array
    {
        // TODO: Adapt to work without store code https://github.com/magento/catalog-storefront/issues/417
        $storageName = $this->storageState->getCurrentDataSourceName(
            [VariantService::EMPTY_STORE_CODE, ProductVariant::ENTITY_NAME]
        );

        $minDocCount = $strict ? count($values) : 1;
        try {
            $queryResult = $this->query->searchAggregatedFilteredEntries(
                $storageName,
                ProductVariant::ENTITY_NAME,
                ['option_value' => $values],
                'id',
                $minDocCount
            );
            $variantIds = \array_column($queryResult, 'id');
        } catch (\Throwable $e) {
            $this->logger->error('Error while trying to fetch product variants by option values: ' . $e);
            throw $e;
        }
        return $variantIds;
    }
}
