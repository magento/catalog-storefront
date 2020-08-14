<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CustomStoreResolver
{
    /**
    * @var StoreManagerInterface
    */
    private $storeManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var array
     */
    private $mappedStores;

    /**
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param array $mappedStores
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        array $mappedStores = []
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->mappedStores = $mappedStores;
    }

    /**
     * Resolve store ID by store code
     *
     * @param string $storeCode
     * @return string|mixed
     */
    public function resolveStoreId(string $storeCode)
    {
        //workaround for tests
        if (empty($this->mappedStores)) {
            $this->mappedStores = $this->setMappedStores();
        }
        return $this->mappedStores[$storeCode] ?? '1';
    }

    /**
     * Resolve store Code by store id
     *
     * @param string $storeId
     * @return string|mixed
     */
    public function resolveStoreCode(string $storeId)
    {
        //workaround for tests
        if (empty($this->mappedStores)) {
            $this->mappedStores = $this->setMappedStores();
        }
        $idToCode = array_flip($this->mappedStores);
        return $idToCode[$storeId] ?? $idToCode['1'];
    }

    /**
     * Retrieve mapped stores, in case if something went wrong, retrieve just one default store
     *
     * @return array
     */
    private function setMappedStores(): array
    {
        try {
            // @todo eliminate store manager
            $stores = $this->storeManager->getStores(true);
            $storesToIds = [];
            foreach ($stores as $store) {
                $storesToIds[$store->getCode()] = (string)$store->getId();
            }
        } catch (\Throwable $e) {
            $storesToIds['default'] = '1';
        }

        return $storesToIds;
    }
}
