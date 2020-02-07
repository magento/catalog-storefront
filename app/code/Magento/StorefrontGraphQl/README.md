# Overview

Module Magento_StorefrontGraphQl has the following responsibilities:  

- Provides mechanism to remove resolvers from GraphQl schema, which should be covered by Storefront service (\Magento\StorefrontGraphQl\Plugin\SchemaReader)
- Collect and provide scopes which need to be passed from GraphQl to Storefront service (\Magento\StorefrontGraphQl\Model\Query\ScopeProvider::getScopes)
- Provide mechanism to invoke Storefront service (\Magento\StorefrontGraphQl\Model\ServiceInvoker::invoke) from GraphQl resolver
- Transform retrieved data from Storefront service to appropriate format
