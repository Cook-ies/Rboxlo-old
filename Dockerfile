FROM php:fpm-alpine

RUN apk add oniguruma-dev
RUN apk add nginx

RUN docker-php-ext-install mbstring
RUN docker-php-ext-install pdo_mysql

RUN rm -rf /var/www/*
RUN mkdir /run/nginx

RUN mkdir /var/www/Application
RUN mkdir /var/www/Data

ADD Website/Public /var/www/html
ADD Website/Application /var/www/Application
ADD Website/Data /var/www/Data
ADD Website/Submodules /var/www/Submodules

RUN chmod +x /var/www/Data

COPY Website/Packaging/Version /var/www/Packaging/Version
COPY .git/refs/heads/master /var/www/Packaging/Hash

COPY Website/PHP.ini /usr/local/etc/php/php.ini

COPY api-keys.json Website/Data/api-keys.json

COPY Website/NGINX/NGINX.conf      /etc/nginx/conf.d/default.conf
COPY Website/NGINX/Locations.conf  /etc/nginx/snippets/locations.conf
COPY Website/NGINX/Domains.conf    /etc/nginx/snippets/domains.conf
COPY Website/NGINX/Custom.conf     /etc/nginx/snippets/custom.conf

COPY Branding/Main/Big.png /var/www/html/html/img/brand/big.png
COPY Branding/Main/Small.png /var/www/html/html/img/brand/small.png
COPY Branding/Backdrops/Main.png /var/www/html/img/backdrops/about.png
COPY Branding/Backdrops/Bricks.png /var/www/html/img/backdrops/admin.png

RUN ln -s /var/www/Data/Thumbnails /var/www/html/html/img/thumbnails
RUN ln -s /var/www/Data/Client /var/www/html/api/setup/files

EXPOSE 8080

COPY Website/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/bin/sh", "/usr/local/bin/entrypoint.sh"]