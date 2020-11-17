### General flow

To run tests for the Storefront Application project we use [GitHub App](https://github.com/magento-commerce/engcom-githubapp-pr-mts-builds) which allows configuring repositories, environment settings and other things to run tests from PR on GitHub 

#### Flow:
- add a comment in PR, e.g. `@magento run all tests` (see [full list](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/development/Running-tests-on-PR.md))
 - Currently, it's configured only for next repos: https://github.com/magento/catalog-storefront, https://github.com/magento/commerce-data-export
GitHub App will ask “Magento Test API” to run tests in PR with [configuration](https://github.com/magento-commerce/engcom-githubapp-pr-mts-builds/blob/master/app/repo-config.js#L652) for SF project
- Test results will be provided in PR

#### Limitation:
- Currently, we run only the EE version of Magento, including all tests for CE verions (tests for B2B edition took > 6 hours, we don't have any b2b features for now)


### Automated Testing Approach _(early phase)_
* We consider GQL API-functional tests as end-to-end tests for Storefront Project (not for Storefront API component itself).
* Storefront API Component itself should be covered by unit and integration tests during development.
* (*) Adding tests to cover integration between Storefront API Component > Message Broker > Magento Export API **is under discussion**.
* The purpose of performing end-to-end testing is to identify system dependencies and ensure that data integrity is maintained between various system components.
* Not all system components are ready right now (NodeJS GraphQL Server, gRPC).
* We can skip running GQL API-functional tests for some time while missing components and infrastructure are under development.
* GQL API-functional tests running should be re-enabled right after all components became available.
* Green GQL API-functional tests build is a must-have as the only proof that all components interact as expected.