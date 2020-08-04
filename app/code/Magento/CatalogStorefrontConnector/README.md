# Overview

Module is deprecated and will be replaced with CatalogMessageBroker
Currently provides
- events for product/category that handled in \Magento\CatalogStorefrontConnector\Model\ProductsQueueConsumer::processMessages and \Magento\CatalogStorefrontConnector\Model\CategoriesQueueConsumer::processMessages
- sync command

Module Magento_CatalogStorefrontConnector has the following responsibilities:

- Connects Magento Monolith to Catalog Storefront service
- Collects and transfers product/category data to Catalog Storefront pipeline

# Internal behaviour


## Events

To collect product data from Magento we only need listen to "catalog search indexer" event and collect processed ids
To collect category data from Magento we only need listen to "category save" event and collect processed ids

Eventually any change on Magento side will affect catalog search indexer. But to support existing functional tests we have to listen to several extra events:
  - product save 
  - stock status save
  - system configuration change

To collect product/category data Magento_*Extract modules are used

## Internal data transfer

Module declares 2 queues to collect product/category changes:
- storefront.catalog.product.connector
- storefront.catalog.category.connector

These queues hold changed entity id to, which will be processed later in consumer to avoid performance degradation during entity save
Message format is described in \Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface

## Automation tests modification
To enable existing tests work with new Storefront Storage we were forced to implement some changes regarding queue consumers run during tests execution.

### API-functional tests:

- "Catalog Storefront" consumers will be executed after all data fixtures (which were declared in test or class DocBlock) created.
    - *Needed to:* submit all created data to Storefront storage.
  
  To implement this the `\Magento\TestFramework\Bootstrap\WebapiDocBlock` class created which modifies parent method `_getSubscribers` and adds subscription to `Magento\TestFramework\Annotation\QueueTrigger` class.
  As a result the `\Magento\TestFramework\Annotation\QueueTrigger::startTest` will be called each test execution.
  NOTE: the consumers will be executed only if current test is instance of `GraphQlAbstract`
    
- "Catalog Storefront" consumers will be executed on each "Catalog Storefront" plugin call
    - *Needed to:* immediately update storefront data after making changes in the tests
    
  Extending of CatalogStorefront plugins added to support data submission to storage for products and categories create/update events:
  `storefront-ce/dev/tests/integration/etc/di/preferences/graphql.php`:
  ```php
    ...
    \Magento\CatalogStorefrontConnector\Plugin\CollectCategoriesDataForUpdate::class
    => \Magento\StorefrontTestFixer\CategoryOnUpdate::class
    ...
  ```
  The main idea of this modification is:
    - Run called plugin
    - Then execute storefront consumers for submit new data to storage
    
  ```php
    $result = parent::afterExecute($subject, $result, $entityIds, $useTempTable);
    
    $objectManager = Bootstrap::getObjectManager();
    /** @var ConsumerInvoker $consumerInvoker */
    $consumerInvoker = $objectManager->get(\Magento\TestFramework\Workaround\ConsumerInvoker::class);
    $consumerInvoker->invoke(true);

    return $result;
  ```
  To prevent consumers execution during fixtures creation the `$invokeInTestsOnly` parameter added which is `false` by default. If this parameter is false - application will check it save/update procedure has been called it test or in fixture and consumers will not be executed in case of call from fixture.
  
- Storage will be cleared after GraphQl related test execution.
    - *Needed to:* prevent occasional data usage and logic exceptions in another tests

  Made by extending GraphQl related tests from the `\Magento\GraphQl\AbstractGraphQl` class and including logic of storage cleaning `\Magento\TestFramework\TestCase\GraphQlAbstract::clearCatalogStorage` to `TearDown()` method.


### Integration tests:

- "Catalog Storefront" consumers will be executed in GraphQl related tests in SetUp() method
    - *Needed to:* submit all created data to Storefront storage
  
    Made by extending GraphQl related tests from the `\Magento\GraphQl\AbstractGraphQl` class and including logic consumers execution `\Magento\GraphQl\AbstractGraphQl::processCatalogQueueMessages` to `SetUp()` method.

- Sometimes wee need to execute consumers in integration tests directly. In this case it's better to create private method which can execute storefront consumers:
    ```php
    /** @var \Magento\Framework\MessageQueue\ConsumerFactory $consumerFactory */
    $consumerFactory = $objectManager->create(\Magento\Framework\MessageQueue\ConsumerFactory::class);
    foreach ($consumers as $consumerName) {
        $consumer = $consumerFactory->get($consumerName, self::BATCHSIZE);
        $consumer->process(self::BATCHSIZE);
    }
    ```
  Or add this logic to "Abstract" class and extend from it if consumers execution needed in more that one test class

# Logging

In case of error occurs log file storefront-catalog.log will be created

To enable debug logging add the following configuration to env.php:

```
'dev' => [
    'debug' => [
        'debug_logging' => 1,
        'debug_extended' => 1, // extended info will be added. Be aware of log size.
    ]
]
```

In case of debug logging is enabled log file storefront-catalog-debug.log will be created