FROM php:8.4.6-apache

RUN a2enmod rewrite

RUN apt-get update \
    && apt-get install -y libzip-dev git wget libicu-dev libpq-dev acl libjpeg-dev libpng-dev libfreetype6-dev \
    && apt-get clean

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd calendar intl

COPY docker/php.ini /usr/local/etc/php/
COPY docker/security.conf /etc/apache2/conf-enabled/security.conf
COPY docker/apache.conf /etc/apache2/sites-enabled/000-default.conf

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN wget https://getcomposer.org/download/2.8.6/composer.phar \ 
    && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer

COPY . /var/www

WORKDIR /var/www

ENTRYPOINT ["/entrypoint.sh"]