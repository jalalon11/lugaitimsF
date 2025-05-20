#!/bin/bash

# Turn off treating deprecation warnings as errors
export PHP_INI_SCAN_DIR=/etc/php/8.2/cli/conf.d
echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE" > /tmp/custom_php.ini
export PHPRC=/tmp/custom_php.ini

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize

echo "Deployment completed successfully!"
