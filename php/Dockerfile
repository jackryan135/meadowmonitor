FROM php:7.2.7-fpm-alpine3.7
RUN apk update; \
    apk upgrade;
RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite