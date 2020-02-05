# Overview

Module Magento_CatalogStorefront provide Catalog Storefront implementation and has the following responsibilities:

TBD...

## Storage


TBD...

Add storage configuration to env.php file:
```
    'catalog-store-front' => [
        'connections' => [
            'default' => [
                'protocol' => 'http',
                'hostname' => 'localhost',
                'port' => '9200',
                'username' => '',
                'password' => '',
                'timeout' => 3
            ]
        ],
        'timeout' => 60,
        'alias_name' => 'catalog_storefront',
        'source_prefix' => 'catalog_storefront_v',
        'source_current_version' => 1
    ],
```

## Logging

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