#!/usr/bin/env bash

USER_ID=${UID:-1000}
GROUP_ID=${GID:-1000}

chown -R ${USER_ID}:${GROUP_ID} /var/www || echo "Some files could not be changed"

composer dump-autoload -n --optimize

composer install

php artisan cache:clear
php artisan migrate --force
php artisan vendor:publish --tag=laravel-assets --ansi
php artisan optimize

# if [ ! -d /.composer ]; then
#     mkdir /.composer
# fi

# chmod -R ugo+rw /.composer

 mkdir /var/www/html/storage/logs
chmod -R ugo+rw /var/www/storage/logs

php-fpm