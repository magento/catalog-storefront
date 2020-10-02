# Plan to introduce back office APIs

## Old state of data exchange between back office (Magento) and storefront

![Old state](https://github.com/magento/catalog-storefront/blob/wiki-images/images/old-state.png?raw=true)

1) See \Magento\CatalogStorefrontConnector\Plugin\ProductUpdatesPublisher

2) See \Magento\CatalogStorefrontConnector\Model\ProductsQueueConsumer

3) See \Magento\CatalogStorefront\Model\MessageBus\Consumer

## Intermediate state of data exchange (for product data only, would be similar for categories)

![Intermediate state](https://github.com/magento/catalog-storefront/blob/wiki-images/images/intermediate-state.png?raw=true)

1") See \Magento\CatalogExport\Model\Indexer\IndexerCallback

2", 3", 4") See \Magento\CatalogMessageBroker\Model\MessageBus\Consumer

## Desired state (for products data only, would be similar for categories)

![Desired state](https://github.com/magento/catalog-storefront/blob/wiki-images/images/desired-state.png?raw=true)

Approach for introducing back office APIs
* After first PR is merged, we gradually add more data to back office API
* We make massage broker use added fields in the story where we add these fields, see \Magento\CatalogMessageBroker\Model\MessageBus\Consumer
* In one of the stories for exposing new fields we are going to replace PHP interfaces with interfaces generated based on proto
* Existing api functional tests should pass
* After import API will be introduced soon, we may need to add missing fields to import API when adding these fields to export API
* All data added to back office API should be exposed in GraphQl
* Currently storefront operates on IDs, we may want to revise this in the future back office API should return both in intermediate state
