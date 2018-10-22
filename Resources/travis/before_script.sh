#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

mkdir -p "$HOME/.php-cs-fixer"

# go into the parent folder and pull a full magento 2 ce project, to do all tests.
echo "==> Installing Magento 2 CE (Version $magento) over composer create-project ..."
cd ..
composer create-project "magento/community-edition:$magento" magento-ce
cd "magento-ce"

# require the elasticsuite extension to make it usable (autoloading)
echo "==> Requiring smile/elasticsuite from the dev-$TRAVIS_BRANCH branch"
composer require "smile/elasticsuite:dev-$TRAVIS_BRANCH"

echo "==> Installing Magento 2"
mysql -uroot -e 'CREATE DATABASE magento2;'
php bin/magento setup:install -q --admin-user="admin" --admin-password="123123q" --admin-email="admin@example.com" --admin-firstname="John" --admin-lastname="Doe" --db-name="magento2"

echo "==> Copying the current build to the Magento 2 installation."
cp -R ../magento2/* vendor/smile/elasticsuite/

# enable the extension, do other relavant mage tasks.
echo "==> Enable extension, do mage tasks..."
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento setup:di:compile
