## **Overview**

Thank you for your interest in contributing to the Storefront API Project! As part of our journey towards microservices and distributed deployments, we are beginning to decouple all Catalog storefront capabilities from the Magento monolith into an independent storefront application that can be scaled independently.

The benefits of this storefront application will be:

* Highly optimized read APIs for significantly better storefront performance.
* Independent and more frequent updates that deliver business value.
* Highly optimized GraphQL service that can be scaled independently.

##  Scope for Phase 1 

* Existing Catalog GraphQL Coverage 
* Search w/ Layered Navigation
* Separate Catalog Storage 
* Separate Catalog & Search Elastic Indexes
* Categories & Product Queries
* Bulk API support 
* Patch support for Bulk API
* gRPC compatibility for Storefront APIs
* Node.js implementation of GraphQL Schema


## **Catalog Storefront Service**

Catalog service provides shoppers access to products and categories and their attributes. Catalog service is not a source of truth for the data it stores and basically is a
projection of the connected management systems like PIM, ERP, etc.

###  **Consequences for data and storage:**

* Due to its origin, the data in catalog service is eventually consistent. 
* The structure of the stored data is optimized to service storefront reads scenarios. Data denormalized.
* The data is partitioned per store view. The store view is selected as an optimal data distinguisher.

###  **Catalog storefront service will provide read and import APIs.**

* Read API's will be exposed to the shopper through the BFF layer such as GraphQL.
* Import API will not be exposed to the public. The purpose of the import API is to populate the catalog database with the data received from the various sources.

## **Integration**

Integration schema has to fulfill the following acceptance criteria:

* The storefront catalog could be deployed separately and does not rely on a source of information it consumes.
* Source for the information could be disabled, replaced, or partially replaced. (Real-life example, AEM allows to override just a few attributes for each product, all other attributes should be pulled from Magento).
* Integration logic should not be a part of the back-office system because of a lack of extensibility - we cannot put enough logic into the external back-office system.
* Another reason, we may need to evolve this layer more rapidly to adjust per discovered cases.

### **Message Broker**

As a result, we come out to a new application - the message broker which will listen to events emitted by the back-office. Each time the message broker receives a new message it performs back lookup for the updated entity data through the API. The data received from a back-office will be correspondingly transformed into the format
supported by the catalog storefront import API. The message broker synchronously calls the storefront import APIs. (If import will require queue for the processing of the messages this queue will be added as a private implementation of storefront catalog service).

The various systems can be connected to the storefront catalog through the dedicated message broker. As a result, the dedicated message broker can consume API specific per each back-office. Because the message broker does not expose APIs such component can be easily disabled, replaced, or upgraded. Message broker should be capable of deduplicating events received from the back-office. This is mandatory optimization for the MVP.

Technically we do not have strict topics, so different systems may provide the same information (Example AEM sometimes wants to override some of the attributes of the products) or aggregate catalog data across the various providers (Example customer has deployed single store as a set of independent Magento instances). So, a merchant
can connect as many systems as the merchant's business requires.

The exceptional case: Integration with Magento.

At the moment Magento does not expose web API that could fulfill requirements of Catalog storefront application.
In the scope of the project, we have to implement a new module that will expose the web API which returns catalog data required by the storefront scenarios per store view.

The next data should be exposed in Magento:
* Product system attributes.
* Product prices.
* EAV attributes.
* Product images and videos.
* Product Options (customizable, downloadable, gift card options).
* Product variants (configurable variants, bundle variants).
* Product to categories assignments.
* Category system attributes.
* Product attributes metadata.

[Optional] By introducing extractor API for Magento we have enough control to deduplicate messages even before they were emitted. And do not emit messages if Magento did not really change the data. (Example1: the product has been saved but no properties were changed, such an event should be ignored; Example 2: Magento should not issue a new event if the product changed inventory records only.). This option is available for the Magento data exporter only. See implementation example https://github.com/magento/commerce-data-export

In the general case, the messages filtration should happen at the import step. 

## **Catalog Import API**

Catalog service import is web API to populate catalog service data.
Catalog service does not perform any significant data transformation on saving, so the structure of stored entities will be quite similar to the exposed import API.
The import filters and ignores duplicate messages or messages which do not change already saved data.
Import API should be tolerant of partial information. In an ideal situation, the import will receive updated properties only and an entity identifier. 

As a consequence:
* All save operation should be performed as PATCH updates.
* All fields of the import message are optional except entity id.

Catalog service has the following import APIs:

* Import Products including prices, stock status, options, and images.
* Import Categories.
* Import Product Variants.
* Import Product Metadata.