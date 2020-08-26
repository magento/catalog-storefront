<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Helper;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for resolving store_code and store_id relationship
 */
class CustomStoreResolver
{
    private const DEFAULT_STORE_VIEW_CODE = 'default';
    private const DEFAULT_STORE_ID = '1';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $mappedStores;

    /**
     * @param StoreManagerInterface $storeManager
     * @param array $mappedStores
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        array $mappedStores = []
    ) {
        $this->storeManager = $storeManager;
        $this->mappedStores = $mappedStores;
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
        $mappedStores = $this->getMappedStores();
        $idToCode = array_flip($mappedStores);
        return $idToCode[$storeId] ?? $idToCode[self::DEFAULT_STORE_ID];
    }

    /**
     * Retrieve mapped stores, in case if something went wrong, retrieve just one default store
     *
     * @return array
     */
    private function getMappedStores(): array
    {
        if (empty($this->mappedStores)) {
            try {
                // @todo eliminate store manager
                $stores = $this->storeManager->getStores(true);
                foreach ($stores as $store) {
                    $this->mappedStores[$store->getCode()] = (string)$store->getId();
                }
            } catch (\Throwable $e) {
                $this->mappedStores[self::DEFAULT_STORE_VIEW_CODE] = self::DEFAULT_STORE_ID;
            }
        }
        return $this->mappedStores;
    }
}
