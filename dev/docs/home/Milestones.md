**Update** source of truth: https://airtable.com/tbljIPtXfyyENuY7H/viw5uN7n9xF7LlUlp?blocks=hide

The goal of this document:
- provide details about the left amount of work for [Phase 1](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/home/Home.md) Scope for Phase1 section
- create milestones 

Below the list of all un-finished work which we need to organise in milestone 

### Existing Catalog GraphQL Coverage

**Note:** all items related to GQL Coverage sections related only to SF API. The same tasks have to be done in scope of GraphQL Server part (see [diagram](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/onboarding/Services-responsibilities.md))

1. Complex products & product options ([design document](https://github.com/magento/architecture/pull/411/))
    1. Downloadable product
    1. Bundle product
    1. Configurable product
    1. Gift Card product
    1. Grouped product
    1. Custom Options
    1. Shopper Input (entered) options
1. Product Variants ([design document](https://github.com/magento/architecture/pull/411/))
1. Price Books ([design document](https://github.com/magento/architecture/pull/405/))
    1. special price
    1. tier prices
    1. price range
1. Catalog (product and category) permissions ([design document](https://github.com/magento/architecture/pull/421))
1. Shared Catalogs
1. Url Rewrites
1. Media Gallery (unified approach)
1. Product Reviews (only "GET" API) (https://github.com/magento/architecture/blob/master/design-documents/graph-ql/coverage/catalog/product-reviews.graphqls)
    1. reviews
    1. rating summary
1. Linked Products 
    1. related_products
    1. upsell_products
    1. crosssell_products
1. Staging & Preview 
1. Option Selection Optimization (https://github.com/magento/architecture/blob/627ee5f805fd451a8962dcbb055bf18ee00c3e35/design-documents/graph-ql/coverage/catalog/configurable-options-selection.graphqls)


### Search w/ Layered Navigation
1. Search Service ([phase1](https://wiki.corp.adobe.com/pages/viewpage.action?spaceKey=EntComm&title=Search+service+phase+1), [design document](https://github.com/magento/architecture/pull/417))
2. Layered Navigation

### gRPC compatibility for Storefront APIs

1. gRPC Server
    1. gRPC server for Storefront Application
    1. Stateful application with Roadrunner for Storefront Application
    1. setup gRPC server on Cloud (P.SH)
    1. setup gRPC server on Jenkins
2. GQL server (nodejs)
    1.  gRPC client: send gRPC request to SF API
    1. setup GraphQL server (nodejs) on Jenkins

### Node.js implementation of GraphQL Schema
Map all existing SF API and from "Complex products & product options" 
TODO: verify whole GQL schema to answer "what resolvers should be mapped"
