# Overview

This repository responsible for 2 different services (will be split in future):
- Message Broker: connect Backoffice and Storefront application.
- Storefront Application: provides Read/Write API through gRPC

### Message Broker Responsibilities
- do callback request (REST) for actual data to Export API
- map Export API format to Storefront API format
- Write data (through gRPC) to Storefront API


### Storefront Application Responsibilities:
- provide Read API for specific attributes, scope
- store data in an efficient way in own database(s)

## Dependecies

### Repository Dependencies 
- https://github.com/magento/saas-export/ (Provide API to Export entities from Magento to any subscribed consumer)

### 3d part dependecnies (composer packages)
- google/protobuf
- grpc/grpc
- spiral/php-grpc
- nette/php-generator

