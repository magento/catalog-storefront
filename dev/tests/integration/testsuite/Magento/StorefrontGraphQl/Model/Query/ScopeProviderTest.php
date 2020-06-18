<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\StorefrontGraphQl\Model\Query;

use Magento\Customer\Model\Group as CustomerGroup;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * @magentoAppArea graphql
 */
class ScopeProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\StorefrontGraphQl\Model\Query\ScopeProvider
     */
    private $scopeProvider;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->scopeProvider = $this->objectManager->get(ScopeProvider::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetScopesWithLoggedInUser()
    {
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');
        /** @var Session $session */
        $session = $this->objectManager->get(Session::class);
        $session->loginById($customer->getId());
        /** @var \Magento\GraphQl\Model\Query\ContextFactory $contextFactory */
        $contextFactory = $this->objectManager->get(\Magento\GraphQl\Model\Query\ContextFactoryInterface::class);

        $context = $contextFactory->create();

        $this->assertEquals(
            ['store' => $customer->getStoreId(), 'customer_group' => $customer->getGroupId()],
            $this->scopeProvider->getScopes($context)
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetScopesAsGuest()
    {
        /** @var \Magento\GraphQl\Model\Query\ContextFactory $contextFactory */
        $contextFactory = $this->objectManager->get(\Magento\GraphQl\Model\Query\ContextFactoryInterface::class);

        $context = $contextFactory->create();

        $this->assertEquals(
            ['store' => 1, 'customer_group' => CustomerGroup::NOT_LOGGED_IN_ID],
            $this->scopeProvider->getScopes($context)
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetScopesWithoutStore()
    {
        $this->expectExceptionMessage("There is no store in extension attributes");
        $this->expectException(\Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException::class);
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');
        $extensionAttributesFactory = $this->objectManager->get(
            ExtensionAttributesFactory::class
        );
        $extensionAttributes = $extensionAttributesFactory->create(
            ContextInterface::class
        );
        $extensionAttributes->setCustomerGroupId($customer->getGroupId());

        $context = $this->objectManager->create(
            ContextInterface::class,
            [
                'userType' => 0,
                'userId' => $customer->getId(),
                'extensionAttributes' => $extensionAttributes
            ]
        );

        $this->scopeProvider->getScopes($context);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetScopesWithoutGroupId()
    {
        $this->expectExceptionMessage("Customer group id wasn't found");
        $this->expectException(\Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException::class);
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');
        $extensionAttributesFactory = $this->objectManager->get(
            ExtensionAttributesFactory::class
        );

        $storeId = $customer->getStoreId();
        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $store->load($storeId, 'store_id');

        $extensionAttributes = $extensionAttributesFactory->create(
            ContextInterface::class
        );
        $extensionAttributes->setStore($store);
        $extensionAttributes->setCustomerGroupId(null);
        $context = $this->objectManager->create(
            ContextInterface::class,
            [
                'userType' => 0,
                'userId' => $customer->getId(),
                'extensionAttributes' => $extensionAttributes
            ]
        );

        $this->scopeProvider->getScopes($context);
    }
}
