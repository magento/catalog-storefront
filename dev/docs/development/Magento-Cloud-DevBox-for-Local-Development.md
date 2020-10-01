This is official Magento DevBox developed originally for Magento Cloud scenarios.
Based on Docker Compose.

Pros:
- official Magento image, has will continue to have Magento support, including compatibility with any Magento changes
- close to production state of the application in Magento Cloud

Cons:
- more oriented for testing scenarios, though `developer` mode is in progress and will be improving

As part of this project, it is currently recommended for:

1. testing purposes
2. DevBox track (should be announced soon)

### Initial Setup

#### Prerequisites

1. PHP
1. Composer
1. GitHub token
1. Docker
1. mutagen.io

#### Setup

1. Clone https://github.com/magento-architects/storefront-cloud-project.git and switch to corresponding feature branch branch.
```
git clone -b <branch> git@github.com:magento-architects/storefront-cloud-project.git
```
Base branch is `production`.
Add environment variable `GITHUB_TOKEN` to access necessary repositories: `export GITHUB_TOKEN=<your_token>`.
Follow https://help.github.com/en/github/authenticating-to-github/creating-a-personal-access-token-for-the-command-line to generate your token.

2. Follow https://devdocs.magento.com/cloud/docker/docker-mode-developer.html instructions to generate the project and start DevBox.

* Make sure Docker has at least 6GB of RAM.
* Skip step 1 about "application template". This repo is a ready project.
* `auth.json` is not needed for ECP project, skip this step.
* `composer install` clones all specified Magento repos (see [.magento.env.yaml](https://github.com/magento-architects/storefront-cloud-project/blob/production/.magento.env.yaml) or [composer.json](https://github.com/magento-architects/storefront-cloud-project/blob/production/composer.json)), this may take a while.
   * If you don't have all required environment dependencies, you can try to run Composer with `--ignore-platform-reqs`. This step is needed mostly do download and link the repositories.

3. Add `127.0.0.1 magento2.docker` to hosts file

Magento should be available at https://magento2.docker/

##### Steps summary

For Mac OS.
Create an executable file `init_project.sh` with the following content:
```
#!/usr/bin/env bash

set -ex

# Clone project
mkdir "$1" && cd "$1"
git clone -b "$3" git@github.com:magento-architects/storefront-cloud-project.git .

# Set GitHub token env var
set +x
export GITHUB_TOKEN=$2
echo "export GITHUB_TOKEN=$2" >> ~/.bash_profile
set -x

# Clone repositories and dependencies
composer install --ignore-platform-reqs

# Start DevBox
docker-compose up -d
./mutagen.sh

# wait a little bit for files to sync, or next steps can fail
set +x
while [[ ! -z $(mutagen list | grep Status: | grep -v 'Watching for changes') ]]
do
  echo "Waiting: files are syncing..."
  sleep 1
done
echo "Files are synced. Proceed."
set -x

# Update dependencies on real environment
docker-compose run deploy composer install --no-scripts

# Deploy Magento application to DevBox
docker-compose run deploy cloud-deploy
docker-compose run deploy cloud-post-deploy

# Add DevBox Magento domain to hosts
sudo -- sh -c "echo '127.0.0.1 magento2.docker' >> /etc/hosts"
```

Review and adjust the script if needed and run as following:
```
./init_project.sh <project-dir> <github-token> <branch>
```
- `<project-dir>` - new dir where the project will be located. The script creates it.
- `<github-token>` - your GitHub token
- `<branch>` - feature branch of this repository

Please contribute to help provide scripted steps for other OS.