FROM php:8.3-apache

WORKDIR /var/www/html

COPY . /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && chown -R www-data:www-data /var/www/html

ENV PROXIMADECK_CONFIG=/var/www/html/config/applications.yaml

EXPOSE 80
