#!/bin/bash

# Enter html directory
cd /var/www/html/jet

sudo composer install

# Clear any previous cached views
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize the application
php artisan config:cache
php artisan optimize
php artisan route:cache

# Change rights
chmod 777 -R bootstrap/cache
chmod 777 -R storage
chmod 777 -R public

# Bring up application
php artisan up