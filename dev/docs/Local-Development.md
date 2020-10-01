This document will describe options for local development, current agreements and limitations

### DevBox options
- Magento Cloud DevBox https://github.com/magento/catalog-storefront/wiki/Magento-Cloud-DevBox-for-Local-Development
- Unofficial DebBox (recommended for now) https://github.com/magento/catalog-storefront/wiki/Andrii-Lugovyi-DevBox-for-Local-Development
- Local DebBox https://github.com/magento/catalog-storefront/wiki/Custom-DevBox-for-Local-Development


### Agreements
1. Manage Dependencies. For GIT installation we have to collect dependencies that not present in root composer.json.  
`php dev/tools/install-dependencies.php .`  ([source](https://github.com/magento/catalog-storefront/blob/develop/dev/tools/install-dependencies.php))
