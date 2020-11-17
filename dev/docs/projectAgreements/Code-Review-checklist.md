Here is a “Code Review checklist" for the Storefront Application project that needs to be followed during CR process.

 
- Story AC is completed
- proposed changes correspond to [Magento Technical Vision](https://devdocs.magento.com/guides/v2.2/coding-standards/technical-guidelines.html)
  - we can have some “exceptions” e.g. for a temporary solution (task need to be created) 
- changes covered with integration/api-function tests
  - check existing coverage
  - add new coverage related to story
  - Expected results it test verified with data from fixture
- Backward compatibility
  - for now, only changes of existing et_schema.xml fields should be verified
  - needed changes confirmed with @Anton Kaplia
 - Export API (et_schema.xml) and SF API schemas (proto schema) are reflected in the codebase
  - prerequisite: story branch created with all needed generated classes according to proposes schema-changes
  - DTO classes does not contain any manual changes (Magento\CatalogExportApi\*, Magento\CatalogStorefrontApi\*)
- Class usage: magento/catalog-storefront repo don't use directly classes from magento/commerce-data-export repo and vise-verse
  - Check composer.json dependencies
  - note: Can be automated by static test
- Legacy code is deleted
  - Any Data Providers present in Connector part  (Magento\CatalogStorefrontConnector, Magento\*Extractor modules)
  - And Data Providers from Export API (magento/commerce-data-export repo) that is not relevant anymore
  - Any DTO for Export API/SF API which does not reflect current schema: et_schema, proto schema
  - Any “mapper” on Message Broker (between Export API and SF API)
    - if mapper still needed, verify fields used in mapping, remove not relevant fields
 

As a part of “final review" until covered with the automated test:
- No un-expected logs in var/log directory during simple flow “product created … product available through SF API”
 
 