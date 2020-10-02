In the future, we plan to have a lot of repositories (potentially in different languages) that will be responsible only for one service (one task).

However now, we have a lot of responsibilities inside one repository which simplify development workflow but causes a lot of mess with understanding responsibility segregation.

**This document provides generic rules and explanation about different services within existing repositories**

***

## Communication rules
- Basically, we can't have a direct (on code level) call from one service (repository) to another
- Communication between different services should be done over network protocol (REST/gRPC)

***


## Diagram 
This simplified diagram highlights the main "parts" of Storefront Application (GraphQL Server and Storefront API) and responsibilities between them


![image](https://user-images.githubusercontent.com/416649/94712735-7eb33a80-030f-11eb-9244-a8d6349fb8f5.png)


## Responsibilities
### Export API. 
Source: https://github.com/magento/saas-export/tree/develop-storefront   
**Provide API to Export entities from Magento to any subscribed consumer**   
Implement 2 different strategies:   
- Push strategy (used by PRex): push changes to remote end-point called Injection Service
- Pull strategy (used by Storefront Application): use [Event Notification](https://martinfowler.com/articles/201701-event-driven.html) approach to send an event with entity id to Message Bus. Message broker do back request to obtain actual data from Backoffice

By fact contract of Export API is described in et_schema.xml

### Message Broker. 
Source: https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/CatalogMessageBroker   
**Connect Backoffice and Storefront application.**   

Responsibilities:
- do callback request (REST) for actual data to Export API
- map Export API format to Storefront API format
- Write data (gRPC) to Storefront API


### Storefront Application. 
Source: https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/CatalogStorefront   
**Provides Read/Write API**   
Responsibilities:
- provide Read API for specific attributes, scope
- store data in an efficient way in own databases

## Modules that will be eliminated (transition period)

### Extension for Backoffice dependent on saas-export repo
Source: [CatalogExport](https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/CatalogExport), [CatalogExportAPI](https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/CatalogExportAPI)   
Will be eliminated in https://github.com/magento/catalog-storefront/issues/185



### Old Export API
Source: [CatalogExtractor](https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/CatalogExtractor), any "*Extractor" module   
**The previous implementation of Export API. Will be migrated to new Export API**   
Will be eliminated after migration all existing Data Providers to Export API

### Connector 
Source: https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/CatalogStorefrontConnector   
Responsibilities:
- expose events "storefront.catalog.category.update", "storefront.catalog.product.update" which publish product ids on different events: product saved, category assigned to product, store created...

## Legacy approach (historical notes)

### GQL Extension for Backoffice 
Source: [Magento/CatalogStorefrontGraphQl](https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/CatalogStorefrontGraphQl) , [Magento/StorefrontGraphQl](https://github.com/magento/catalog-storefront/tree/develop/app/code/Magento/StorefrontGraphQl)  
**Override Magento GraphQL Resolvers to make request to Storefront**   
Note: should be replaced with GraphQL server written on NodeJS   
Responsibilities:
- override Magento GQL resolvers
- map GQL Query to Request acceptable for Storefront APP
- proxy request to _original_ Magento GQL Resolver for fields deprecated in GQL schema


