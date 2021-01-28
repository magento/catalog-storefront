#!/bin/sh

cp composer.json.standalone composer.json
cp app/etc/catalog-storefront/di.xml.standalone app/etc/catalog-storefront/di.xml
cp app/etc/db_schema.xml.standalone app/etc/db_schema.xml
cp app/storefront_catalog_product_autoload.php app/autoload.php
cp app/storefront_catalog_product_bootstrap.php app/bootstrap.php
