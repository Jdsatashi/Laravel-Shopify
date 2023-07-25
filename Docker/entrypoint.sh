#!/bin/bash
# Run install or update composer dependencies
composer update

# Run Laravel configuration cache
php artisan config:cache

# Run Laravel optimization
php artisan optimize

# Start PHP-FPM
php-fpm