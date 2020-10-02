If you like your DevBox the most, feel free to use it (with some limitations, see bellow).
In this case, the project still needs to be composed.
And, again, there are multiple options for this.

### `app`-based Code Structure

You can use a script for linking files from multiple repos into Magento working folder.
Find script here https://github.com/magento/partners-magento2ee/blob/2.4-develop/dev/tools/build-ee.php
Though it is part of EE, it can link arbitrary repositories.
See https://github.com/duhon/magento-docker/blob/storefront/clone_repos for an example usage for this project.

### Composer-based Structure

Use Composer repository `type`=`path` to compose your `composer.json` to link repositories.
See an example in https://github.com/magento/storefront-cloud-project/blob/ECP-513/composer.json.
Limitation of this method is files not included in any package, won't be linked in an easy setup.

### Limitations

This approach should not be used for tickets on the DevBox track, as in this track we will require you to use Magento Cloud DevBox.