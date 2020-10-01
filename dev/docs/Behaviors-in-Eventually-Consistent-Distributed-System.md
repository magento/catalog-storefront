Introduction of Catalog Store Front service makes Magento system distributed, with some data being projected from the source of truth to the places where it will be read.
More services to come, as the system grows in complexity to allow more scalability, replaceability and other benefits.
This document describes expectations from such system, as the behavior of the system differs comparing to a monolithic application.

# General Expectations from a Distributed System

1. Data is eventually consistent.
2. Data synchronization may be significantly delayed in case of a component failure.
3. Data inconsistency may be invisible for a user.

The goal should be to built a system that behaves according to the user expectations.
User expectations may be handled either by building a consistent system (the one that handles data inconsistencies gracefully and doesn't allow inconsistent data being displayed to the user), by providing relevant messages to the user to handle the expectations, or by both.

More details about building distributed systems can be found in:

* [Designing Data-Intensive Applications: The Big Ideas Behind Reliable, Scalable, and Maintainable Systems](https://www.goodreads.com/book/show/23466395-designing-data-intensive-applications)
* [Building Microservices: Designing Fine-Grained Systems](https://www.goodreads.com/book/show/22512931-building-microservices)

# Scenarios

## Product Management and Retrieval

Current state:

* Catalog GraphQL relies on new Catalog Service with product data projection
* Search relies on the product source of truth in Magento monolith

As a result, GraphQL queries may return unexpected result when product data is not yet synced to the Catalog Storefront service.

### Product Creation

1. Admin creates a product
2. User requests the product via GraphQL API

**Result:**

- As products query uses Search first to find product ID, it finds the product because it works with the original source for products data.
- Then Search makes a request to the Catalog service.
- If the product is not synced to the Catalog (yet or due to a failure), it returns an error. As a result:
   1. An error is logged in Catalog service logs for every such GQL request
   2. an Internal Server Error is returned as a GQL result to the client

* [ ] The above behavior is approved by PO (@nrkapoor)

Notes:

1. Our desired state is that Search relies on data provided by Catalog service and not on original data. In this case, such situation should not be possible. But until we achieve desired state, we get errors.

### Product Deletion

❗ This is scenario covering the desired state, where Search relies on product data provided by Catalog service, and not on the original data.

Preconditions:

1. A product exists in the system
2. The product is synced to Catalog SF service

Steps:

1. Admin removes the product
2. 2. User requests the deleted product via GraphQL API

**Result:**

- The product deletion is already synced to Catalog service, but not yet synced to the Search service
- As a result, Search finds already deleted product
- Then Search makes a request to the Catalog service
- As Catalog has the product removed, it returns an error. As a result:
   1. An error is logged in Catalog service logs for every such GQL request
   2. An Internal Server Error is returned as a GQL result to the client

* [ ] The above behavior is approved by PO (@nrkapoor)