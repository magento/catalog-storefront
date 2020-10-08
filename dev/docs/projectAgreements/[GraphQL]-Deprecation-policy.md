This document describes a deprecation policy for GraphQL schema in scope of Storefront Application project


## How to deal with fields deprecated in GQL?

- GraphQL Server (NodeJS) will proxy requests to Magento Monolith GQL (see diagram [here](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/onboarding/Services-responsibilities.md)) to retrieve fields marked as deprecated in GQL
- Deprecated in GQL fields should not be exposed through Storefront API


## How to deal with fields not present in Storefront API 

- do not expose field through Storefront API that we "don’t want to have"
- proceed with 1st option below and add field to SF or deprecate from GQL after analyze

Problem: What options do we have to solve the problem “field X is not deprecated in GQL, but field X is not present in Storefront API”

We have only 3 options here:

1. analyze use-cases with PWA/ECE and

   1.1. if no real use-cases: deprecate field in GQL with prosing another approach

   1.2. if have real use-case: add field X to Storefront API
2. treat field X as “deprecated” and do proxy on Monolith
3. add field X to Storefront API

Here some details regarding "1st" option and problem in general

We want to consider SF API as independent API which should reflect Storefront needs and not blindly copy existing GQL schema.
That's why we should follow 1st option and analyze GQL fields that we don’t want to have on Storefront API and deprecate/add them only after analyzing existing use-cases
