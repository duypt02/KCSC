FROM php:7.4-apache

RUN apt-get update
RUN apt-get install nano
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
RUN a2enmod headers


WORKDIR /var/www/html

ADD ./duy.vn/ /var/www/html
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 777 /var/www/html

VOLUME /var/www/html

ENTRYPOINT service apache2 start && /bin/bash
