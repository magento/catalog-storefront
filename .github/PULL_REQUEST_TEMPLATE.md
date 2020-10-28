<!---
    Thank you for contributing to Magento.
    To help us process this pull request we recommend that you add the following information:
     - Summary of the pull request,
     - Issue(s) related to the changes made,
     - Manual testing scenarios
    Fields marked with (*) are required. Please don't remove the template.
-->

<!--- Please provide a general summary of the Pull Request in the Title above -->

### Description (*)
<!---
    Please provide a description of the changes proposed in the pull request.
    Letting us know what has changed and why it needed changing will help us validate this pull request.
-->

### Related Pull Requests
<!-- related pull request placeholder -->

### Fixed Issues (if relevant)
<!---
    If relevant, please provide a list of fixed issues in the format magento/catalog-storefront#<issue_number>.
    There could be 1 or more issues linked here and it will help us find some more information about the reasoning behind this change.
-->
1. Fixes magento/catalog-storefront#<issue_number>


### Questions or comments
<!---
	If relevant, here you can ask questions or provide comments on your pull request for the reviewer
	For example if you need assistance with writing tests or would like some feedback on one of your development ideas
-->

### Code Review Checklist (*)

See detailed [checklist](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/projectAgreements/Code-Review-checklist.md)

- [ ] Story AC is completed
- [ ] proposed changes correspond to [Magento Technical Vision](https://devdocs.magento.com/guides/v2.2/coding-standards/technical-guidelines.html)
- [ ] new or changed code is covered with web-api/integration tests (if applicable)
  - expected results in test verified with data from fixture
- [ ] no backward incompatible changes
- [ ] Export API (et_schema.xml) and SF API schemas (proto schema) are reflected in the codebase
  - prerequisite: story branch created with all needed generated classes according to proposes schema-changes
  - DTO classes do not contain any manual changes (Magento\CatalogExportApi\*, Magento\CatalogStorefrontApi\*)
- [ ] Class usage: magento/catalog-storefront repo don't use directly classes from magento/saas-export repo and vise-verse
  - Check composer.json dependencies
- [ ] Legacy code is deleted
  - Any Data Providers present in Connector part  (Magento\CatalogStorefrontConnector, Magento\*Extractor modules)
  - And Data Providers from Export API (magento/saas-export repo) that is not relevant anymore
  - Any DTO for Export API/SF API which does not reflect current schema: et_schema, proto schema
  - Any “mapper” on Message Broker (between Export API and SF API)
    - if mapper still needed, verify fields used in mapping, remove not relevant fields


