Local Setup without DevBox

## Technology stack

Catalog Storefront Application (SF APP) uses the same stack of technologies that Magento 2.4.

https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html

All required technologies must installed in the system including:

[Composer](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#composer-latest-stable-version)

[Database](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#database)

[PHP](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#database) with [required PHP extensions](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#database)

[Elasticsearch](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#elasticsearch)

[SSL](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#elasticsearch)

[Required system dependencies](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements-tech.html#elasticsearch)

[RabbitMQ](https://devdocs.magento.com/guides/v2.4/config-guide/mq/rabbitmq-overview.html)

If you have some issues with setup take a look into [prerequisites](https://devdocs.magento.com/guides/v2.4/install-gde/prereq/prereq-overview.html) to make sure everything installed as needed.

## Required repositories

You need to have access to the following repositories in order to contribute to SF APP

https://github.com/magento/magento2.git

https://github.com/magento/partners-magento2ee.git

https://github.com/magento/catalog-storefront.git

https://github.com/magento/catalog-storefront-ee.git

https://github.com/magento/saas-export.git

https://github.com/magento/data-solutions-services-id.git

https://github.com/magento/magento-services-connector.git

To check the latest actual repositories and branches visit the [following link](https://git.corp.adobe.com/ecp/magento-repos/blob/cloud/sources.yaml)

## Project initialization

Create a folder for project. Let's assume it will be `/var/www`. Navigate to the folder and do the following steps:

Clone and link Magento with Enterprise edition
```
git clone git@github.com:magento/magento2.git magento2ce -b develop-storefront && \
git clone git@github.com:magento/partners-magento2ee.git magento2ee -b develop-storefront && \
cd magento2ee && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../magento2ee && \
cd ..
```

if  you need to install B2B edition (currently not used), then clone and link it as well:
```
git clone git@github.com:magento/magento2b2b.git -b 1.2.0-develop && \
mkdir -p magento2b2b/dev/tools && \
cp magento2ee/dev/tools/build-ee.php magento2b2b/dev/tools/build-ee.php && \
cd magento2b2b && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../magento2b2b && \
cd ..
```

Clone and link `catalog-storefront`, `catalog-storefront-ee`, `saas-export`, `data-solutions-services-id` and `magento-services-connector`
```
git clone git@github.com:magento/catalog-storefront.git && \
cd catalog-storefront && \
git checkout -B develop origin/develop && \
cd .. && \
mkdir -p catalog-storefront/dev/tools && \
cp magento2ee/dev/tools/build-ee.php catalog-storefront/dev/tools/build-ee.php && \
cd catalog-storefront && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../catalog-storefront && \
cd .. && \
git clone git@github.com:magento/catalog-storefront-ee.git && \
cd catalog-storefront-ee && \
git checkout -B develop origin/develop && \
cd .. && \
mkdir -p catalog-storefront-ee/dev/tools && \
cp magento2ee/dev/tools/build-ee.php catalog-storefront-ee/dev/tools/build-ee.php && \
cd catalog-storefront-ee && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../catalog-storefront-ee && \
cd .. && \
git clone git@github.com:magento/saas-export.git -b develop-storefront && \
mkdir -p saas-export/dev/tools && \
cp magento2ee/dev/tools/build-ee.php saas-export/dev/tools/build-ee.php && \
cd saas-export && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../saas-export && \
cd .. && \
git clone git@github.com:magento-commerce/data-solutions-services-id.git -b develop-storefront && \
mkdir -p data-solutions-services-id/dev/tools && \
cp magento2ee/dev/tools/build-ee.php data-solutions-services-id/dev/tools/build-ee.php && \
cd data-solutions-services-id && \
php dev/tools/build-ee.php --ce-source ../magento2ce/app/code/Magento --ee-source ../data-solutions-services-id && \
cd .. && \
git clone git@github.com:magento/magento-services-connector.git -b develop-storefront && \
mkdir -p magento-services-connector/dev/tools && \
cp magento2ee/dev/tools/build-ee.php magento-services-connector/dev/tools/build-ee.php && \
cd magento-services-connector && \
mkdir -p ../magento2ce/app/code/Magento/ServicesConnector && \
php dev/tools/build-ee.php --ce-source ../magento2ce/app/code/Magento/ServicesConnector --ee-source ../magento-services-connector && \
cd ..
```

If you already have cloned repositories with code you can link the code to magento2ce with the following:
```
cd magento2ee && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../magento2ee && \
cd ../catalog-storefront && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../catalog-storefront && \
cd ../catalog-storefront-ee && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../catalog-storefront-ee && \
cd ../saas-export && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../saas-export && \
cd ../data-solutions-services-id && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../data-solutions-services-id && \
cd ../magento-services-connector && \
php dev/tools/build-ee.php --ce-source ../magento2ce --ee-source ../magento-services-connector
cd ..
```

## Install Magento

Run composer to install dependencies.
```
cd magento2ce && \
composer install
```

Create a database with name `magento` if not exist.
```
mysql -uroot -hdb -e "DROP DATABASE IF EXISTS magento; create database magento;"
```

Sample CLI command to install Magento:
```
bin/magento setup:install \
   --backend-frontname=admin \
   --admin-lastname=Admin \
   --admin-firstname=Admin \
   --admin-email=magento@example.com \
   --admin-user=admin \
   --admin-password=123123q \
   --db-name=magento \
   --db-host=db \
   --elasticsearch-host=elastic
```

Run the following script to update required module dependencies for git-based installation:
```
php dev/tools/install-dependencies.php .
```

Setup configuration:
```
bin/magento config:set web/unsecure/base_url http://magento.test/ && \
bin/magento config:set web/secure/base_url http://magento.test/ && \
bin/magento config:set web/seo/use_rewrites 1 && \
bin/magento config:set dev/template/allow_symlink 1 && \
bin/magento config:set admin/security/admin_account_sharing 1 && \
bin/magento config:set admin/security/session_lifetime 31536000 && \
bin/magento config:set system/media_storage_configuration/media_database default_setup && \
bin/magento config:set catalog/search/engine elasticsearch${ELASTICSEARCH_VERSION} && \
bin/magento config:set catalog/search/elasticsearch${ELASTICSEARCH_VERSION}_server_hostname elastic && \
bin/magento setup:config:set -n --amqp-host=rabbit --amqp-user=$AMQP_USER --amqp-password=$AMQP_PASSWORD
```
Please set proper values for
$MAGENTO_DOMAIN, $ELASTICSEARCH_VERSION, $AMQP_USER and $AMQP_PASSWORD.

Run reindex:
```
bin/magento index:reindex
```

Navigate to `app/etc/env.php` and add the following configuration to the file:

```
    'catalog-store-front' => [
        'connections' => [
            'default' => [
                'protocol' => 'http',
                'hostname' => 'elastic',
                'port' => '9200',
                'username' => '',
                'password' => '',
                'timeout' => 3
            ]
        ],
        'timeout' => 60,
        'alias_name' => 'catalog_storefront',
        'source_prefix' => 'catalog_storefront_v',
        'source_current_version' => 1
    ],
    'system' => [
        'default' => [
            'services_connector' => [
                'sandbox_gateway_url' => 'https://qa-api.magedevteam.com/',
                'production_gateway_url' => 'https://int-api.magedevteam.com/',
                'api_portal_url' => 'https://account-stage.magedevteam.com/apiportal/index/index/'
            ]
        ]
    ]
```
Set `queue`>`consumers_wait_for_messages` to `0`:
```
    'queue' => [
        'consumers_wait_for_messages' => 0,
```

Run the following CLI commands to apply changes in a config:
```
bin/magento app:config:import && \
bin/magento setup:upgrade && \
bin/magento cache:clean
```

Start consumers:
```
bin/magento queue:consumers:start catalog.product.export.consumer & \
bin/magento queue:consumers:start catalog.category.export.consumer & \
bin/magento queue:consumers:start storefront.catalog.product.update & \
bin/magento queue:consumers:start storefront.catalog.category.update &
```

## Checking that SF APP works.

Navigate to admin and create a simple product.
Send get request to Elasticsearch and check if it contains a created index (`catalog_storefront_v1_default_product`)
and product information:
`curl http://elastic:9200/_search?pretty`
