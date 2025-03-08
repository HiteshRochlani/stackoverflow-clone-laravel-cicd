#!/bin/bash
chown -R $USER:www-data storage bootstrap/cache public
chmod -R 775 storage bootstrap/cache
find storage bootstrap/cache -type f -exec chmod 664 {} \;
php-fpm -y /usr/local/etc/php-fpm.conf -R
