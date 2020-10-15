# Code Sources

A final product developed on the project consists of multiple repositories.  
New repositories can be added as needed.  
Different features may work with different set of repositories (not only branches).  
List of required repositories can be found [here](https://github.com/magento/storefront-cloud-project/blob/production/.magento.env.yaml).    
Possible ways to understand which branches to use in a task:

1. Ticket description. Each ticket should contain repositories and branches to work with. If this is not the case for the issue you get, please highlight this in the project's Slack channel and/or on the stand up meeting.
2. Slack channel. This would be the most up-to-date information, but it's easy to lose a track of what was said in the channel, so use with caution.

# Local Development

There are multiple options setup local development environment.
Please read the following documents to install development environment and choose the option that works best for you.

1. [Recommended DevBox](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/development/Andrii-Lugovyi-DevBox-for-Local-Development.md)  
1. [Local installation](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/development/Local-Install.md)  
1. [Magento Cloud DevBox](https://github.com/magento/catalog-storefront/blob/develop/dev/docs/development/Magento-Cloud-DevBox-for-Local-Development.md) (under construction)   

## Setup for web API/integration tests

Add a parameter to dev/tests/api-functional/config/install-config-mysql.php.dist (notice that dashes used here)

```
'consumers-wait-for-messages'  => '0',
```

Copy dev/tests/integration and dev/tests/api-functional from storefront (ce and ee repos) into dev/tests/integration and dev/tests/api-functional accordingly.

Or you can update env.php to set this setting to "0":
```
    'queue' => [
        'consumers_wait_for_messages' => 0,
         ....
    ]
```

## Troubleshooting

### Docker services die unexpectedly

Check how much RAM you dedicate to Docker and whether other Docker services are running.
To be on safe side, it is recommended to dedicate 6 GB to docker when working with the project. This assumes no other services are running. If you run other Docker services, adjust accordingly.

### Docker services don't start

This may be caused by conflicts with other Docker services running on your machine.
Check if you run any other services and whether they try to use same resources.
One of the common resource conflict is port allocation.
Use `docker-compose.yml` as a reference for which ports are used by Magento DevBox and make sure they don't conflict with other running services.