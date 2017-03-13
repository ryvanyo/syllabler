FROM php:5-apache
MAINTAINER Gabriel Trabanco Llano <gtrabanco@fwok.org>

RUN a2enmod rewrite

COPY config/php.ini-default /usr/local/etc/php/php.ini

WORKDIR /var/www/html
COPY website/www .

COPY website/templates ../templates
COPY library ../library

EXPOSE 80



