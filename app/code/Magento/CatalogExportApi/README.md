# Overview

Magento_CatalogExportApi is responsible for providing API to expose catalog data for storefront.

###Generating DTO file
Required Software
> composer require nette/php-generator

How to Use

By file path 
> php bin/magento dto:generate --file /var/www/html/app/code/Magento/CatalogExportApi/etc/et_schema.xml

By module
> php bin/magento dto:generate --module Magento_CatalogExportApi

