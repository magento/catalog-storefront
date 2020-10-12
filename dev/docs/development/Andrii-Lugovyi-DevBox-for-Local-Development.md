Based on Docker Compose.

See https://github.com/duhon/magento-docker/tree/storefront for the version adjusted for this project.
Look at https://github.com/duhon/magento-docker/tree/storefront#how-to-install as it already includes information necessary for this project.

❗ Make sure `.env` file is updated with the repositories and branches specific to your task. Original `.env` file may be outdated. See [Code Sources](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/onboarding/Project-Onboarding.md) of the Onboarding page to find out which repositories and branches you need.

Pros:
- repositories linking is adjusted for development scenarios: files are linked and so can be directly committed to corresponding repos)

Cons:
- file structure does not correspond to production state
- dependencies of packages are not taken into account

As part of this project, it is currently recommended for:

1. development based on Magento 2.4 (issue [#17](https://github.com/magento/catalog-storefront/issues/17) and related tickets)