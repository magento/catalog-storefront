# Overview

Module Magento_CatalogStorefrontConnector has the following responsibilities:

- Connect Magento Monolith with Catalog Storefront service
- Collect and transfer product/category data to Catalog Storefront pipeline (push data to storefront.catalog.data.consume queue)
 

# Internal behaviour


## Events

To collect product data from Magento we only need to listen "catalog search indexer" event and collect processed ids
To collect category data from Magento we only need to listen "category save" event and collect processed ids

Eventually any change on Magento side will affect catalog search indexer. But to support existing functional tests we have to listen several extra events:
  - product save 
  - stock status save
  - system configuration change

To collect product/category data Magento_*Extract modules are used

## Internal data transfer

Module declares 2 queues to collect product/category changes:
- storefront.catalog.product.connector
- storefront.catalog.category.connector

Theses queues hold changed entity id to avoid performance issue during entity save

# Logging

In case of error log file storefront-catalog.log will be created

To enable debug logging add the following configuration to env.php:

```
'dev' => [
    'debug' => [
        'debug_logging' => 1,
        'debug_extended' => 1, // extended info will be added. Be aware about log size
    ]
]
```

In case of debug logging is enabled log file storefront-catalog-debug.log will be created