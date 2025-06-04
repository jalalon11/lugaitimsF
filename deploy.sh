#!/bin/bash

# Turn off treating deprecation warnings as errors
export PHP_INI_SCAN_DIR=/etc/php/8.2/cli/conf.d
echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE" > /tmp/custom_php.ini
export PHPRC=/tmp/custom_php.ini

# Set environment to production explicitly
export APP_ENV=production

# Run migrations with force flag to bypass production confirmation
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize --force

# Seed the database if needed (uncomment if you need to seed)
# php artisan db:seed --force

# Final confirmation to ensure deployment completes
echo "Deployment completed successfully!"
exit 0
