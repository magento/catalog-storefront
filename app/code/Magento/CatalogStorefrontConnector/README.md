# Overview

Module Magento_CatalogStorefrontConnector has the following responsibilities:

- Connect Magento Monolith with Catalog Storefront service
- Collect and transfer product/category data to Catalog Storefront pipeline (push data to storefront.catalog.data.consume queue)
 

# Internal behaviour


## Events
- Listen events to collect changes in product/category entities
  - product save 
  - catalog search reindex
  - category save
  - stock status save


Magento_*Extract modules are used to collect data

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