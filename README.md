# SF APP Project Documents
* [Project Overview:](https://github.com/magento/catalog-storefront/dev/docs/home/Home.md)
  * [Scopes (Milestones)](https://github.com/magento/catalog-storefront/dev/docs/home/Milestones.md)
* [Onboarding:](https://github.com/magento/catalog-storefront/dev/docs/onboarding/Project-Onboarding.md)
   * [Repositories](https://github.com/magento/storefront-cloud-project/blob/production/.magento.env.yaml)
   * [Services Responsibilities](https://github.com/magento/catalog-storefront/dev/docs/onboarding/Services-responsibilities.md)
   * [Time Off Tracking List](https://github.com/magento/catalog-storefront/dev/docs/onboarding/Time-Off-Tracking.md)
* Development:
   * [DevBox](https://github.com/magento/catalog-storefront/dev/docs/development/Local-Development.md)
   * [Running Tests on PR](https://github.com/magento/catalog-storefront/dev/docs/development/Running-tests-on-PR.md)
* Agreements:
   * [Branching Strategy](https://github.com/magento/catalog-storefront/dev/docs/projectAgreements/Branching-strategy.md)
   * [Code Review Checklist](https://github.com/magento/catalog-storefront/dev/docs/projectAgreements/Code-Review-checklist.md)
   * [[GraphQL] Deprecation Policy](https://github.com/magento/catalog-storefront/dev/docs/projectAgreements/[GraphQL]-Deprecation-policy.md)
   * [System Behavior](https://github.com/magento/catalog-storefront/dev/docs/projectAgreements/Behaviors-in-Eventually-Consistent-Distributed-System.md)
* Design Documents:
   * [Catalog Storefront Service Overview](https://github.com/magento/catalog-storefront/dev/docs/designDocuments/Catalog-Storefront-Service.md)
   * [Plan to Introduce Back Office APIs](https://github.com/magento/catalog-storefront/dev/docs/designDocuments/Plan-to-introduce-back-office-APIs.md)
   * [Product Variants and Type Specific Options](https://github.com/magento/catalog-storefront/dev/docs/designDocuments/Product-variants-and-type-specific-options.md)
   * [Catalog Media Tech Vision](https://github.com/magento/architecture/blob/master/design-documents/media/catalog-images.md)
   * [Configuration Propagation](https://github.com/magento/architecture/blob/master/design-documents/storefront/configuraiton-propagation.md)
   * [Configurable Product Variants](https://github.com/magento/catalog-storefront/dev/docs/designDocuments/Configurable-products-option-variants.md)
   * [Product Variants Services](https://github.com/magento/catalog-storefront/dev/docs/designDocuments/Product-Variants-services.md)
* QA:
   * [Storefront Testing](https://github.com/magento/catalog-storefront/dev/docs/qa/Storefront-Testing.md)
* Demo:
   * [How To Demo "Add Product with Options to Cart"](https://github.com/magento/catalog-storefront/dev/docs/demo/How-To-Demo-"Add-Product-with-Options-to-Cart".md)
* Contacts:
   * [Slack](https://magentocommeng.slack.com/archives/G0157R0PF3J)


# Current Repo Overview
This repository responsible for 2 different services (will be split in future):
- Message Broker: connects Backoffice and Storefront application.
- Storefront Application: provides Read/Write API through gRPC

### Message Broker Responsibilities
- request actual data from Export API (via REST)
- map Export API format to Storefront API format
- write data (through gRPC) to Storefront API

### Storefront Application Responsibilities
- provide Read API for specific attributes, scope
- provide Write API for specific attributes, scope
- store data in an efficient way in own database(s)

### Service repository Dependencies 
- https://github.com/magento/saas-export/ (Provides API to Export entities from Magento to any subscribed consumer)

### 3rd-party dependecnies (composer packages)
- google/protobuf
- grpc/grpc
- spiral/php-grpc
- nette/php-generator

