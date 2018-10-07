# Baze_CustomerEmail

## Architecture

Modules that require theme changes <- Core theme -> Child "skin" themes

**Standalone modules** (This Repo) -> (no dependencies)

## Installation

```bash
# as root
systemctl stop cron
```
```bash
# as user
magento maintenance:enable
composer config repositories.module-customer-email vcs https://github.com/bazedk/module-customer-email
composer require baze/module-customer-email:^1.0
magento module:enable Baze_CustomerEmail
magento setup:upgrade
magento setup:di:compile
magento setup:static-content:deploy en_US da_DK
magento cache:clean
magento cache:flush
magento maintenance:disable
```
```bash
# as root
systemctl restart php7.1-fpm
systemctl start cron
```

## Usage

```bash
# as user
magento customer:email:send --website=code
```
