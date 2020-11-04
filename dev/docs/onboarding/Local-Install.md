Local Setup without DevBox

## Technology stack

Catalog Storefront Application (SF APP) uses a stack of technologies that Magento 2.4 does including technologies required for Catalog Storefront service.

https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html

All required technologies must installed in the system including the following:

[Composer](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#composer)    
[Database](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#database)  
[PHP](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#php) with [required PHP extensions](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#required-php-extensions)    
for gRPC PHP should have an additional set of extensions, see information in [gRPC Server](https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/Grpc) how to set up them.   
[Elasticsearch](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#elasticsearch)    
[SSL](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#ssl)  
[Required system dependencies](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#required-system-dependencies)  

The following technologies required for Catalog Storefront service:
[RabbitMQ](https://devdocs.magento.com/guides/v2.4/config-guide/mq/rabbitmq-overview.html) is an optional for Magento, but mandatory for SF APP.  
[gRPC Server](https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/Grpc)  
[grpcui](https://github.com/fullstorydev/grpcui) is a useful tool to interact with gRPC server via a browser. 
[GraphQL Node Server](https://github.com/magento/graphql/blob/master/docs/DEVELOPMENT.md)

If you have any issues with setup take a look into [prerequisites](https://devdocs.magento.com/guides/v2.4/install-gde/prereq/prereq-overview.html) to make sure everything installed as needed.

## Required repositories  
You need to have access to the [following repositories](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/onboarding/Repositories.md) in order to contribute to SF APP

## Project installation  
 
Mentioned repositories should be installed.  
Use correspond repositories from `magento/partners-magento2{ee|b2b}` to install Enterprise/B2B editions.    

Run composer to install dependencies.
```
composer install
```

Install Magento with [command-line](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-install.html)  
Run the following script to update required module dependencies for git-based installation:
```
php dev/tools/install-dependencies.php .
```

Run reindex:
```
bin/magento index:reindex
```

To set up custom configuration for Elastic Search storage need to add a section `catalog-store-front` to `app/etc/env.php`. Take a look this [document](https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/CatalogStorefront#storage) for more details.  
Run the following CLI commands to apply changes made in a config:
```
bin/magento app:config:import && \
bin/magento setup:upgrade && \
bin/magento cache:clean
```

Start SF APP consumers:  
```
bin/magento queue:consumers:start catalog.product.export.consumer & \
bin/magento queue:consumers:start catalog.category.export.consumer &
```
Consumers processes messages from message queue: fetche data via `Export API` and store them into `SF APP storage`.

## Checking that SF APP works.

1. Navigate to admin and create a simple product.  
1. (optional) send GET request to Elasticsearch to check if it contains a created index (`catalog_storefront_v1_default_product`)
and product information:  
`curl http://elastic:9200/_search?pretty`, where `elastic` is a host of elasticsearch  
1. To ensure data propagated to Storefront you can also do a gRPC call with `https://github.com/fullstorydev/grpcui`
