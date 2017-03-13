FROM php:5.6-apache
MAINTAINER Gabriel Trabanco Llano <gtrabanco@fwok.org>

ENV SYLLABLER_VERSION 0.1.0-beta.4


# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
# Copied from wordpress Dockerfile:
#   https://github.com/docker-library/wordpress/blob/3a1ca61731e6070764d5b7235e3b6617798b8af8/php5.6/apache/Dockerfile
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=2'; \
		echo 'opcache.fast_shutdown=1'; \
		echo 'opcache.enable_cli=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN a2enmod rewrite expires

VOLUME /var/www/html

WORKDIR /var/www/html

COPY website/www .
COPY website/templates ../templates
COPY library ../library
RUN rm nginx.htaccess && mv apache.htaccess .htaccess

EXPOSE 80



