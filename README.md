# Overview.

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

