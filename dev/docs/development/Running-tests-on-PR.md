
:exclamation: Automated tests can be triggered manually with an appropriate comment:
- `@magento run all tests` - run or re-run all required tests against the PR changes
- `@magento run <test-build(s)>` - run or re-run specific test build(s)
For example: `@magento run Unit Tests`

`<test-build(s)>` is a comma-separated list of build names. Allowed build names are:

1. `Database Compare`
2. `Functional Tests CE`
3. `Functional Tests EE`,
4. `Functional Tests B2B`
5. `Integration Tests`
6. `Magento Health Index`
7. `Sample Data Tests CE`
8. `Sample Data Tests EE`
9. `Sample Data Tests B2B`
10. `Static Tests`
11. `Unit Tests`


Here is the supported format of the comment:

    @magento run all tests [against <magento-version>]
    [with env[ironment] PHP <php-version>, [search-engine <search-engine> <search-engine-version>,] [database <database> <database-version>]]
    [with edition[s] <comma-separated-editions>]
    [with extension[s] <comma-separated-extension-repos-with-optional-branch>]
    [without extension[s] <comma-separated-extension-repos-to-exclude>]

or

    @magento run <comma-separated-build-names> [against <magento-version>] 
    [with env[ironment] PHP <php-version>, [search-engine <search-engine> <search-engine-version>,] [database <database> <database-version>]]
    [with edition[s] <comma-separated-editions>]
    [with extension[s] <comma-separated-extension-repos-with-optional-branch>]
    [without extension[s] <comma-separated-extension-repos-to-exclude>]
    
Example of the comment:

    @magento run Unit Tests, Functional Tests EE against 2.4-develop
    with env PHP 7.2, search-engine ElasticSearch 6, database MariaDB 10.2
    with editions EE, B2B
    with extensions magento/security-package:1.0-develop, magento/security-package-ee
    without extension magento/magento2-page-builder-ee
   

full feature list (internal): https://github.com/magento/engcom-githubapp-pr-mts-builds
