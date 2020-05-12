<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class StoreSaveTest
 */
class StoreSaveTest extends GraphQlAbstract
{
    /**
     * Test a product from newly created store
     *
     * @magentoApiDataFixture Magento/Catalog/_files/category_product.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductVisibleInNewStore()
    {
        $newStoreCode = 'new_store';
        $this->createStore($newStoreCode);
        //use case for new storeCode
        $this->assertCategory($newStoreCode);
        $this->assertProduct($newStoreCode);
    }

    /**
     * Test product in store.
     *
     * @param string $storeCodeFromFixture
     * @throws \Exception
     */
    private function assertProduct(string $storeCodeFromFixture)
    {
        $productSku = 'simple333';
        $productNameInFixtureStore = 'Simple Product Three';

        $productsQuery = <<<QUERY
{
  products(filter: { sku: { eq: "%s" } }, sort: { name: ASC }) {
    items {
      id
      sku
      name
    }
  }
}
QUERY;
        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery(
            sprintf($productsQuery, $productSku),
            [],
            '',
            $headerMap
        );
        $this->assertCount(
            1,
            $response['products']['items'],
            sprintf('Product with sku "%s" not found in store "%s"', $productSku, $storeCodeFromFixture)
        );
        $this->assertEquals(
            $productNameInFixtureStore,
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );
    }

    /**
     * Test category in store.
     *
     * @param string $storeCodeFromFixture
     * @throws \Exception
     */
    private function assertCategory(string $storeCodeFromFixture)
    {
        $categoryName = 'Category 1';
        $categoryQuery = <<<QUERY
{
    categoryList(filters: {name: {match: "%s"}}){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery(
            sprintf($categoryQuery, $categoryName),
            [],
            '',
            $headerMap
        );
        $this->assertCount(
            1,
            $response['categoryList'],
            sprintf('Category with name "%s" not found in store "%s"', $categoryName, $storeCodeFromFixture)
        );
        $this->assertEquals(
            $categoryName,
            $response['categoryList'][0]['name'],
            'Category name in fixture store is invalid.'
        );
    }

    /**
     * Creates store by store code.
     *
     * @param string $storeCode
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createStore(string $storeCode): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

        /** @var \Magento\Store\Model\Store $store */
        $store = $objectManager->create(\Magento\Store\Model\Store::class);

        if (!$store->load($storeCode)->getId()) {
            $store->setCode($storeCode)
                ->setWebsiteId($storeManager->getWebsite()->getId())
                ->setGroupId($storeManager->getWebsite()->getDefaultGroupId())
                ->setName($storeCode)
                ->setSortOrder(10)
                ->setIsActive(1);
            $store->save();

            /** @var $indexer \Magento\Framework\Indexer\IndexerInterface */
            $indexer = $objectManager->create(\Magento\Indexer\Model\Indexer::class);
            $indexer->load(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID);
            $indexer->reindexAll();
        }
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeStore('new_store');
    }

    /**
     * Deletes store by store code.
     *
     * @param string $storeCode
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function removeStore(string $storeCode): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Registry $registry */
        $registry = $objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var \Magento\Store\Model\Store $store */
        $store = $objectManager->get(\Magento\Store\Model\Store::class);
        $store->load($storeCode, 'code');
        if ($store->getId()) {
            $store->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
