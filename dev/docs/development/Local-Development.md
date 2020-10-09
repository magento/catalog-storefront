This document will describe options for local development, current agreements and limitations

### DevBox options
- Magento Cloud DevBox https://github.com/magento/catalog-storefront/blob/develop/dev/docs/development/Magento-Cloud-DevBox-for-Local-Development.md
- Unofficial DebBox (recommended for now) https://github.com/magento/catalog-storefront/blob/develop/dev/docs/development/Andrii-Lugovyi-DevBox-for-Local-Development.md
- Local DebBox https://github.com/magento/catalog-storefront/blob/develop/dev/docs/development/Custom-DevBox-for-Local-Development.md


### Agreements
1. Manage Dependencies. For GIT installation we have to collect dependencies that not present in root composer.json.  
`php dev/tools/install-dependencies.php .`  ([source](https://github.com/magento/catalog-storefront/blob/develop/dev/tools/install-dependencies.php))
