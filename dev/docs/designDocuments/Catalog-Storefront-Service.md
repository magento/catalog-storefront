## Overview

Catalog service provides shoppers access to products and categories and their attributes.
Catalog service is not a source of truth for the data it stores and basically is a projection of the connected management systems like PIM, ERP, etc.

Consequences for data and storage:
* Due to its origin, the data in catalog service is eventually consistent.
* The structure of the stored data is optimized to service storefront reads scenarios. Data denormalized.
* The data is partitioned per store view. The store view is selected as optimal data distinguisher.

Catalog storefront service has to provide read and import APIs.
* Read API's will be exposed to the shopper through the BFF layer such as GraphQL.
* Import API, will not be exposed to the public. The purpose of the import API is to populate the catalog database with the data received from the various sources.

## Integration 
Integration schema has to fulfill the following acceptance criteria:

* The storefront catalog could be deployed separately and does not rely on a source of information it consumes.
* Source for the information could be disabled, replaced, or partially replaced. (Real-life example, AEM  allows to override just a few  attributes for each product, all other attributes should be pulled from Magento).
* Integration logic should not be a part of the back-office system because of lack of extensibility  -  we can not put enough logic into the external back-office system. Another reason, we may need to evolve this layer more rapidly to adjust per discovered cases. 

### Message Broker
As a result, we come out to a new application - the message broker which will listen to events emitted by the back-office. Each time the message broker receives a new message it performs back lookup for the updated entity data through the API. The data received from a back-office will be correspondingly transformed into the format supported by the catalog storefront import API. The message broker synchronously calls the storefront import APIs. (If import will require queue for the processing of the messages this queue will be added as a private implementation of storefront catalog service).

![](https://app.lucidchart.com/publicSegments/view/2dbbdf13-4a91-4765-ae18-5f5719b95e6f/image.png)

The various systems can be connected to the storefront catalog through the dedicated message broker. As a result, the dedicated message broker can consume API specific per each back-office. Because the message broker does not expose APIs such component can be easily disabled, replaced or upgraded.

Message broker should be capable of deduplicating events received from the back-office. This is mandatory optimization for the MVP.

![](https://app.lucidchart.com/publicSegments/view/19717137-e690-469f-8574-fba2777fbc83/image.png)

Technically we do not have strict topics, so different systems may provide the same information (Example AEM sometimes wants to override some of the attributes of the products) or aggregate catalog data across the various providers (Example customer has deployed single store as a set of independent Magento instances). So a merchant can connect as many systems as the merchant's business requires.

### Export  API 

In general case Export API is represented as back-office web API which allows the message broker to fetch the data.
Also, the Export extension should implement an event which will notify external observers that product data was changes and synchronization required.

In most cases, we have no control under the external export API except the API we should build for Magento.

##### Particular Magento Export API & Integration

At the moment Magento does not expose web API that could fulfill requirements of Catalog storefront application.
In the scope of the project, we have to implement a new module that will expose the web API which returns catalog data required by the storefront scenarios per store view.
It should be enough data to fulfill requirements of the GraphQL tier that we have now.

The required data we have to collect from Magento could be logically separated into two logical groups.
* original entities data.
* data calculated by some business rules, example: catalog rules, prices, product assignments.

Original product data are accessible for synchronization since an entity saved.
Calculated data is eventually consistent, and could be synced after generated in Magento.
The calculated data is represented in Magento through the indexation layer.
So, we can say that the export API could be eventually consistent only,
and we have to be sure that we did not fire notification about product change before data calculated.
![](https://app.lucidchart.com/publicSegments/view/1d4324b1-dc34-4f8e-9e4a-8f5d17afa2cb/image.png)

#### Preventing chatty communication with Export extension

Magento merchants are managing the catalog in multiple various ways.
We cannot predict how exactly, but we know that frequent situations when catalog synchronization is built programmatically by using REST  API or CSV import.
Synchronization on that level cannot distinguish changes, so even products that do not have changes will cause save operation. 
Such a situation can cause triggering an abnormal number of false messages about product updates.

This case could be resolved by adding additional filtration logic into Magento export extension. The extension will store information about previously synchronized information and triggers external notification about product changes only if the product really changed since the last sync.
Such a solution will dramatically reduce the number of notifications produced by the export extension as the drawback of this solution is that data synchronization time will increase.

![](https://app.lucidchart.com/publicSegments/view/09f137cb-de7f-48cd-be27-a707ff7af4b4/image.png)


#### Data that should be exposed 

The next data should be exposed in Magento:

* Product system attributes.
* Product prices.
* EAV attributes.
* Product images and videos.
* Product options (customizable, downloadable, bundle, gift card options).
* Product variants (configurable variants, bundle variants).
* Product to categories assignments.
* Category system attributes.
* Product attributes metadata.

### Catalog Import API
Catalog service import is web API to populate catalog service data.

Catalog service does not perform any significant data transformation on saving, so the structure of stored entities will be quite similar to the exposed import API.

The import filters and ignores duplicate messages or messages which do not change already saved data.

Import API should be tolerant to partial information. In an ideal situation, the import will receive updated properties only and an entity identifier. As a consequence:

* All save operation should be performed as PATCH updates.
* All fields of the import message are optional except entity id.
Catalog service has the following import APIs:

* Import Products including prices, stock status, options and images.
* Import Categories.
* Import  Product Variants.
* Import Product Metdata.

### Message structure
The structure of each message may vary depends on feed type and business needs, the only two fields required along with all the messages: id and time of the latest modification. Preferably both of the fields should be originated from the back-office system.
Storefront may not use external ID as a primary identifier but has to store the mapping between external and internal IDs.
Because we are assuming that the storefront system may be integrated with various back offices we have to be ready that all these systems will have their own external IDs. As a result, we have to be ready to maintain various mappings.